'use strict';

/// <reference path="../../../js/common.d.ts" />
/// <reference path="../../../js/lodash-3.10.d.ts" />

import {AmeCustomizable, AmeCustomizableViewModel} from '../../pro-customizables/assets/customizable.js';
import {registerBaseComponents} from '../../pro-customizables/ko-components/ame-components.js';
import AmeAcStructure from './ko-components/ame-ac-structure.js';
import AmeAcSection from './ko-components/ame-ac-section.js';
import AmeAcSectionLink from './ko-components/ame-ac-section-link.js';
import AmeAcControl from './ko-components/ame-ac-control.js';
import AmeAcControlGroup from './ko-components/ame-ac-control-group.js';
import AmeAcContentSection from './ko-components/ame-ac-content-section.js';
import {AmeAdminCustomizerBase} from './admin-customizer-base.js';

declare var wsAmeLodash: _.LoDashStatic;
declare const wsAmeAdminCustomizerData: AmeAdminCustomizer.ScriptData;

export namespace AmeAdminCustomizer {
	import Setting = AmeCustomizable.Setting;
	import SettingCollection = AmeCustomizable.SettingCollection;
	import InterfaceStructureData = AmeCustomizable.InterfaceStructureData;
	import InterfaceStructure = AmeCustomizable.InterfaceStructure;
	import unserializeUiElement = AmeCustomizable.unserializeUiElement;
	import unserializeSetting = AmeCustomizable.unserializeSetting;
	import AnySpecificElementData = AmeCustomizable.AnySpecificElementData;
	import CustomizableVmInterface = AmeCustomizableViewModel.CustomizableVmInterface;

	const $ = jQuery;
	const _ = wsAmeLodash;

	registerBaseComponents();
	ko.components.register('ame-ac-structure', AmeAcStructure);
	ko.components.register('ame-ac-section', AmeAcSection);
	ko.components.register('ame-ac-section-link', AmeAcSectionLink);
	ko.components.register('ame-ac-content-section', AmeAcContentSection);
	ko.components.register('ame-ac-control-group', AmeAcControlGroup);
	ko.components.register('ame-ac-control', AmeAcControl);

	export interface ScriptData extends AmeAdminCustomizerBase.ScriptData {
		ajaxUrl: string;
		saveChangesetNonce: string;
		changesetItemCount: number;
		changesetStatus: string;
		refreshPreviewNonce: string;
		initialPreviewUrl: string;
		interfaceStructure: InterfaceStructureData;
	}

	const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
	let prefersReducedMotion = reducedMotionQuery && reducedMotionQuery.matches;
	reducedMotionQuery.addEventListener('change', () => {
		prefersReducedMotion = reducedMotionQuery.matches;
	});

	class CustomizerSettingsCollection extends SettingCollection {
		/**
		 * Settings that have changed since the last save attempt.
		 */
		private pendingSettings: Record<string, Setting> = {};
		/**
		 * Settings that in the process of being sent to the server to be saved.
		 * They might not be saved yet.
		 */
		private sentSettings: Record<string, Setting> = {};
		private currentChangesetRequest: JQueryXHR = null;
		private saveTriggerTimeoutId = null;

		private readonly currentChangeset: KnockoutObservable<Changeset>;

		public readonly changesetName: KnockoutComputed<string>;

		/**
		 * Whether any settings currently have validation errors.
		 */
		public readonly hasValidationErrors: KnockoutComputed<boolean>;

		public readonly isExclusiveOperationInProgress: KnockoutComputed<boolean>;
		private readonly exclusiveOperation: KnockoutObservable<boolean> = ko.observable(false);

		constructor(
			public readonly ajaxUrl: string,
			public readonly saveChangesetNonce: string,
			changesetName: string,
			changesetItemCount: number = 0,
			changesetStatus: string | null = null
		) {
			super();
			const self = this;

			this.currentChangeset = ko.observable(
				new Changeset(changesetName, changesetItemCount, changesetStatus)
			);
			this.changesetName = ko.pureComputed(() => {
				return (self.currentChangeset()?.name()) || '';
			});

			//Automatically save the changeset when any settings change.
			const totalChangeCount = ko.pureComputed(() => {
				const changeset = self.currentChangeset();
				return (changeset ? changeset.currentSessionChanges() : 0);
			});
			totalChangeCount.subscribe(_.debounce(
				(counter) => {
					if (counter > 0) {
						self.queueChangesetUpdate()
					}
				},
				3000,
				{leading: true, trailing: true}
			));

			this.isExclusiveOperationInProgress = ko.pureComputed(() => {
				return self.exclusiveOperation() === true;
			});

			//Keep track of unsaved changes and changesets.
			this.addChangeListener((setting: Setting) => {
				this.pendingSettings[setting.id] = setting;

				let changeset = this.currentChangeset();
				//If the current changeset cannot be modified, create a new one
				//for the changed setting(s).
				if (!changeset?.canBeModified()) {
					changeset = new Changeset();
					this.currentChangeset(changeset);
				}
				//Track the number of changes in the current session.
				changeset.currentSessionChanges(changeset.currentSessionChanges() + 1);
			});
		}

		queueChangesetUpdate(delay: number = 0) {
			if (delay > 0) {
				if (this.saveTriggerTimeoutId !== null) {
					//Replace the existing timeout with a new one.
					clearTimeout(this.saveTriggerTimeoutId);
				}
				this.saveTriggerTimeoutId = setTimeout(() => {
					this.saveTriggerTimeoutId = null;
					this.queueChangesetUpdate(0);
				}, delay);
				return;
			}

			if (this.saveTriggerTimeoutId !== null) {
				return; //Another timeout is already waiting.
			}

			if (this.currentChangesetRequest !== null) {
				//There's an in-progress request, so wait until it's done.
				this.currentChangesetRequest.always(() => {
					//Wait a bit to avoid hammering the server.
					this.queueChangesetUpdate(1000);
				});
				return;
			}

			this.saveChangeset();
		}

		private saveChangeset(status: string = null): JQueryPromise<any> {
			//Do nothing if there are no changes.
			if (_.isEmpty(this.pendingSettings) && (status === null)) {
				return $.Deferred().reject(new Error('There are no changes to save.')).promise();
			}

			if (this.isExclusiveOperationInProgress()) {
				return $.Deferred().reject(
					new Error('Another exclusive changeset operation is in progress.')
				).promise();
			}

			let isExclusiveRequest = (status === 'publish') || (status === 'trash');
			if (isExclusiveRequest) {
				this.exclusiveOperation(true);
			}

			const savedChangeset = this.currentChangeset();

			//Keep a local copy of the settings in case something changes instance
			//properties while the request is in progress (should never happen).
			const settingsToSend = this.pendingSettings;
			this.sentSettings = settingsToSend;
			this.pendingSettings = {};

			const modifiedSettings = _.mapValues(settingsToSend, setting => setting.value());
			const requestData = {
				action: 'ws_ame_ac_save_changeset',
				_ajax_nonce: this.saveChangesetNonce,
				changeset: savedChangeset?.name ?? '',
				modified: JSON.stringify(modifiedSettings),
			};
			if (status !== null) {
				requestData['status'] = status;
			}
			//If the changeset doesn't have a name, it is new.
			if (!savedChangeset?.hasName()) {
				requestData['createNew'] = 1;
			}

			const request = $.ajax({
				url: this.ajaxUrl,
				method: 'POST',
				data: requestData,
				dataType: 'json',
				timeout: 20000,
			});
			this.currentChangesetRequest = request;

			interface ServerValidationResults {
				[settingId: string]: {
					isValid: boolean;
					errors: Array<{ code: string; message: string; }>;
				}
			}

			const self = this;

			function storeValidationResultsFrom(serverResponse) {
				const results: ServerValidationResults = _.get(
					serverResponse,
					['data', 'validationResults']
				);
				if (typeof results !== 'object') {
					return;
				}

				for (const settingId in results) {
					const setting = self.get(settingId);
					if (!setting.isDefined()) {
						continue;
					}

					if (!modifiedSettings.hasOwnProperty(settingId)) {
						continue;
					}
					const sentValue = modifiedSettings[settingId];

					const state = results[settingId];
					if (state.isValid) {
						setting.get().clearValidationErrorsForValue(sentValue);
					} else {
						setting.get().addValidationErrorsForValue(
							sentValue,
							_.filter(state.errors, error => (typeof error.message === 'string'))
						);
					}
				}
			}

			function storeChangesetDetailsFrom(serverResponse) {
				if (!savedChangeset) {
					return;
				}

				//Store the returned changeset name in case a new changeset was created.
				if (!savedChangeset.hasName()) {
					const newName = _.get(serverResponse, ['data', 'changeset']);
					if (typeof newName === 'string') {
						savedChangeset.name(newName);
					}
				}
				//Store the changeset status.
				const newStatus = _.get(serverResponse, ['data', 'changesetStatus']);
				if (typeof newStatus === 'string') {
					savedChangeset.status(newStatus);
				}

				//Store the number of changes in the changeset.
				const newChangeCount = _.get(serverResponse, ['data', 'changesetItemCount']);
				if (typeof newChangeCount === 'number') {
					savedChangeset.knownItemCount(newChangeCount);
				}

				//Was the changeset published? Because changesets are typically moved
				//to trash after publishing, "status" might be "trash" instead of "publish",
				//but we still want to know if it was successfully published.
				const wasPublished = _.get(serverResponse, ['data', 'changesetWasPublished'], null);
				if (wasPublished) {
					savedChangeset.wasPublished(wasPublished);
				}
			}

			request.done(function (response) {
				storeChangesetDetailsFrom(response);
				storeValidationResultsFrom(response);

				//After successfully publishing a changeset, it has no more
				//unsaved changes.
				const isPublished =
					(savedChangeset.status() === 'publish')
					|| (savedChangeset.status() === 'future')
					|| (savedChangeset.wasPublished());
				if (isPublished) {
					savedChangeset.currentSessionChanges(0);
				}

				//After a changeset is published or trashed, it can no longer
				//be edited. We may be able to replace it with a new changeset
				//that was created on the server.
				if (!self.currentChangeset().canBeModified()) {
					const nextChangeset = _.get(response, ['data', 'nextChangeset']);
					if ((typeof nextChangeset === 'string') && (nextChangeset !== '')) {
						self.currentChangeset(new Changeset(nextChangeset));
					}
				}
			});

			request.fail((requestObject: JQueryXHR) => {
				if (typeof requestObject.responseJSON === 'object') {
					storeValidationResultsFrom(requestObject.responseJSON);
					storeChangesetDetailsFrom(requestObject.responseJSON);
				}

				//Add the unsaved settings back to the pending list.
				for (const id in settingsToSend) {
					//Keep only settings that still exist.
					if (this.get(id).isDefined()) {
						this.pendingSettings[id] = settingsToSend[id];
					}
				}

				//We don't automatically retry because the problem might be something
				//that doesn't get better on its own, like missing permissions.
			});

			request.always(() => {
				this.currentChangesetRequest = null;
				this.sentSettings = {};
				if (isExclusiveRequest) {
					this.exclusiveOperation(false);
				}
			});

			return request;
		}

		public getCurrentChangeset(): Changeset {
			return this.currentChangeset();
		}

		/**
		 * Get any unsaved setting changes.
		 *
		 * @returns An object mapping setting IDs to their modified values.
		 */
		public get unsavedChanges(): Record<string, any> {
			//Include both pending settings and sent settings. Sent settings
			//might not be saved yet.
			let unsavedSettings: Record<string, Setting> = {};
			_.defaults(unsavedSettings, this.pendingSettings, this.sentSettings);

			return _.mapValues(unsavedSettings, setting => setting.value());
		}

		public publishChangeset(): JQueryPromise<any> {
			if (this.isExclusiveOperationInProgress()) {
				return $.Deferred()
					.reject(new Error('Another exclusive changeset operation is already in progress.'))
					.promise();
			}
			return this.saveChangeset('publish');
		}
	}

	class Changeset {
		public readonly name: KnockoutObservable<string>;
		public readonly knownItemCount: KnockoutObservable<number>;
		public readonly status: KnockoutObservable<string>;

		/**
		 * The number of times settings have been changed in this changeset
		 * during the current customizer session.
		 *
		 * Note that this is not the same as the number settings in the changeset:
		 * if the same setting is changed X times, this counter will increase by X,
		 * but the changeset will still only have one entry for that setting.
		 */
		public readonly currentSessionChanges: KnockoutObservable<number> = ko.observable(0);

		/**
		 * Once a changeset has been published or deleted, its contents can't be modified any more.
		 * @private
		 */
		private readonly fixedContentStatuses: Record<string, any> =
			{'publish': true, 'trash': true, 'future': true};

		public readonly wasPublished: KnockoutObservable<boolean> = ko.observable(false);

		constructor(name: string = '', knownItemCount: number = 0, initialStatus: string | null = '') {
			this.name = ko.observable(name);
			this.knownItemCount = ko.observable(knownItemCount);
			this.status = ko.observable(initialStatus ?? '');
		}

		public hasName(): boolean {
			const name = this.name();
			return (typeof name === 'string') && (name !== '');
		}

		public canBeModified(): boolean {
			return !this.fixedContentStatuses.hasOwnProperty(this.status());
		}

		public isNonEmpty(): boolean {
			return (this.currentSessionChanges() > 0) || (this.knownItemCount() > 0)
		}
	}

	class SectionNavigation {
		private sectionNavStack: KnockoutObservableArray<string> = ko.observableArray([]);
		private $sectionList: JQuery = null;

		public readonly breadcrumbs: KnockoutObservable<NavigationBreadcrumb[]>;

		constructor() {
			this.$sectionList = $('#ame-ac-container-collection');

			this.$sectionList.on('click', '.ame-ac-section-link', (event) => {
				event.preventDefault()

				const targetId = $(event.currentTarget).data('target-id');
				if (targetId) {
					this.navigateToSection(targetId);
				}
			});

			this.$sectionList.on('click', '.ame-ac-section-back-button', (event) => {
				event.preventDefault()
				this.navigateBack();
			});

			this.breadcrumbs = ko.pureComputed(() => {
				return this.sectionNavStack()
					.map((sectionId) => $('#' + sectionId))
					.filter(($section) => $section.length > 0)
					.map(($section) => {
						return {
							title: $section.find('.ame-ac-section-title .ame-ac-section-own-title')
								.first().text()
						}
					});
			});
		}

		navigateToSection(sectionElementId: string) {
			const $section = $('#' + sectionElementId);
			if ($section.length === 0) {
				return;
			}

			if ($section.hasClass('ame-ac-current-section')) {
				return; //Already on this section.
			}

			//If the requested section is in the navigation stack, navigate back
			//to it instead of putting more sections on the stack.
			const stackIndex = this.sectionNavStack.indexOf(sectionElementId);
			if (stackIndex !== -1) {
				while (this.sectionNavStack().length > stackIndex) {
					this.navigateBack();
				}
				return;
			}

			const $previousSection = this.$sectionList.find('.ame-ac-current-section');
			if ($previousSection.length > 0) {
				this.expectTransition($previousSection, '.ame-ac-section');
				$previousSection
					.removeClass('ame-ac-current-section')
					.addClass('ame-ac-previous-section');
				this.sectionNavStack.push($previousSection.attr('id'));

				$previousSection.trigger('adminMenuEditor:leaveSection');
			}

			this.expectTransition($section, '.ame-ac-section');
			$section.addClass('ame-ac-current-section');

			$section.trigger('adminMenuEditor:enterSection');
		}

		navigateBack() {
			if (this.sectionNavStack().length < 1) {
				return;
			}
			const $newCurrentSection = $('#' + this.sectionNavStack.pop());
			if ($newCurrentSection.length === 0) {
				return;
			}

			const $oldCurrentSection = this.$sectionList.find('.ame-ac-current-section');
			this.expectTransition($oldCurrentSection, '.ame-ac-section');
			$oldCurrentSection.removeClass('ame-ac-current-section ame-ac-previous-section');
			$oldCurrentSection.trigger('adminMenuEditor:leaveSection');

			const $oldPreviousSection = this.$sectionList.find('.ame-ac-previous-section');
			$oldPreviousSection.removeClass('ame-ac-previous-section');

			//Show the new current section.
			this.expectTransition($newCurrentSection, '.ame-ac-section');
			$newCurrentSection.addClass('ame-ac-current-section');
			$newCurrentSection.trigger('adminMenuEditor:enterSection');

			//The next section in the stack becomes the previous section.
			if (this.sectionNavStack().length > 0) {
				this.$sectionList.find('#' + this.sectionNavStack()[this.sectionNavStack().length - 1])
					.addClass('ame-ac-previous-section');
			}
		}

		//Add a special class to sections when they have an active CSS transition.
		//This is used to keep both sections visible while the previous section
		//slides out and the next section slides in.
		expectTransition($element: JQuery, requiredSelector: string) {
			if (prefersReducedMotion) {
				return;
			}

			if ($element.data('ameHasTransitionEvents')) {
				return; //Event handler(s) already added.
			}

			const transitionEvents = 'transitionend transitioncancel';

			$element.addClass('ame-ac-transitioning');

			function transitionEndCallback(event) {
				//Ignore events that bubble from child elements.
				if (!$(event.target).is(requiredSelector)) {
					return;
				}

				$element
					.off(transitionEvents, transitionEndCallback)
					.data('ameHasTransitionEvents', null)
					.removeClass('ame-ac-transitioning');
			}

			$element.data('ameHasTransitionEvents', true);
			$element.on(transitionEvents, transitionEndCallback);
		}
	}

	export interface NavigationBreadcrumb {
		title: string;
	}

	export class AdminCustomizer extends AmeAdminCustomizerBase.AdminCustomizerBase implements CustomizableVmInterface {
		sectionNavigation: SectionNavigation;
		settings: CustomizerSettingsCollection;
		public readonly interfaceStructure: InterfaceStructure;

		private $previewFrame: JQuery = null;

		/**
		 * Preview frame URL.
		 */
		private currentPreviewUrl: string | null = null;
		/**
		 * The default preview URL that can be used when the current frame URL cannot be detected.
		 */
		private readonly initialPreviewUrl: string;
		private previewConnection: ReturnType<typeof AmeAcCommunicator.connectToParent> | null = null;
		private readonly refreshPreviewNonce: string;

		private $saveButton: JQuery = null;

		constructor(scriptData: ScriptData) {
			super(scriptData);

			this.settings = new CustomizerSettingsCollection(
				scriptData.ajaxUrl,
				scriptData.saveChangesetNonce,
				scriptData.changesetName,
				scriptData.changesetItemCount,
				scriptData.changesetStatus
			);
			_.forOwn(scriptData.settings, (data, id) => {
				this.settings.add(unserializeSetting(id, data));
			});

			let sectionIdCounter = 0;

			this.interfaceStructure = unserializeUiElement(
				scriptData.interfaceStructure,
				this.settings.get.bind(this.settings),
				(data: AnySpecificElementData) => {
					switch (data.t) {
						case 'section':
							data.component = 'ame-ac-section';
							//All sections must have unique IDs for navigation to work.
							if (!data.id) {
								data.id = 'autoID-' + (++sectionIdCounter);
							}
							break;
						case 'control-group':
							data.component = 'ame-ac-control-group';
							break;
						case 'control':
							//Tell controls that use number inputs to position the popup
							//slider within the customizer sidebar.
							if (
								(data.component === 'ame-number-input')
								|| (data.component === 'ame-box-sides')
							) {
								data.params = data.params || {};
								data.params.popupSliderWithin = '#ame-ac-sidebar-content';
							}
					}
				}
			);

			//Add the changeset name to the URL (if not already present).
			const currentUrl = new URL(window.location.href);
			if (currentUrl.searchParams.get('ame-ac-changeset') !== this.settings.changesetName()) {
				currentUrl.searchParams.set('ame-ac-changeset', this.settings.changesetName());
				window.history.replaceState({}, '', currentUrl.href);
			}
			//When the changeset name changes, also change the URL. Discourage navigating
			//to the old URL (no pushState()) because the name is only expected to change
			//when the old changeset becomes invalid (e.g. it's deleted or published).
			this.settings.changesetName.subscribe((changesetName) => {
				const url = new URL(window.location.href);
				url.searchParams.set('ame-ac-changeset', changesetName);
				window.history.replaceState({}, '', url.href);
			});

			this.$saveButton = $('#ame-ac-apply-changes');

			//The save button should be enabled when:
			// - There are non-zero changes in the current changeset.
			// - All settings are valid.
			// - The changeset is not in the process of being published, deleted, etc.
			// - The contents of the changeset can be modified (e.g. not already published).
			const isSaveButtonEnabled = ko.pureComputed(() => {
				const changeset = this.settings.getCurrentChangeset();
				return (
					changeset.isNonEmpty()
					&& changeset.canBeModified()
					&& !this.settings.isExclusiveOperationInProgress()
					&& !this.settings.hasValidationErrors()
				);
			});
			//Update button state when the customizer loads.
			this.$saveButton.prop('disabled', !isSaveButtonEnabled());
			//And also on changes.
			isSaveButtonEnabled.subscribe((isEnabled) => {
				this.$saveButton.prop('disabled', !isEnabled);
				//Change the text back to the default when the button is enabled.
				if (isEnabled) {
					this.$saveButton.val(this.$saveButton.data('default-text') ?? 'Save Changes');
				}
			});

			//Handle the "Save Changes" button.
			this.$saveButton.on('click', () => {
				//Show the spinner.
				const $spinner = $('#ame-ac-primary-actions .spinner');
				$spinner.css('visibility', 'visible').show();

				const publishFailNoticeId = 'ame-ac-publish-failed-notice';
				//Remove the previous error notification, if any.
				$('#' + publishFailNoticeId).remove();

				const promise = this.settings.publishChangeset();

				promise.fail((error) => {
					//Show a dismissible error notification.
					let message = 'An unexpected error occurred while saving changes.';
					if (typeof error === 'string') {
						message = error;
					} else if (error instanceof Error) {
						message = error.message;
					} else if (typeof error.responseJSON === 'object') {
						message = _.get(error.responseJSON, ['data', 'message'], message);
					}

					const $notice = $('<div>')
						.attr('id', publishFailNoticeId)
						.addClass('notice notice-error is-dismissible')
						.text(message);

					//WordPress won't automatically add the dismiss button to a dynamically
					//generated notice like this, so we have to do it.
					$notice.append(
						$('<button type="button" class="notice-dismiss"></button>')
							.append('<span class="screen-reader-text">Dismiss this notice</span>')
							.on('click', (event) => {
								event.preventDefault();
								$notice.remove(); //Not as fancy as WP does it.
							})
					);

					const $container = $('#ame-ac-global-notification-area');
					$container.append($notice);
				})

				promise.done(() => {
					this.$saveButton.val(this.$saveButton.data('published-text') ?? 'Saved');

					//The preview could be stale. For example, the color scheme module
					//switches between "actual" and "preview" color schemes dynamically,
					//but the "actual" scheme could change after applying new settings.
					//Let's reload the preview frame to make sure it's up-to-date.
					this.queuePreviewFrameReload();
				});

				promise.always(() => {
					$spinner.css('visibility', 'hidden');
				});
			});

			//Prevent the user from interacting with settings while the changeset is being modified.
			this.settings.isExclusiveOperationInProgress.subscribe((isInProgress) => {
				$('#ame-ac-sidebar-blocker-overlay').toggle(isInProgress);
			});

			this.sectionNavigation = new SectionNavigation();

			//Set up the preview frame.
			this.$previewFrame = $('iframe#ame-ac-preview');

			this.initialPreviewUrl = scriptData.initialPreviewUrl;
			this.refreshPreviewNonce = scriptData.refreshPreviewNonce;

			this.$previewFrame.on('load', () => {
				this.isFrameLoading = false;

				//The URL that was actually loaded might not match the one that
				//was requested (e.g. because there was a redirect).
				this.currentPreviewUrl = null;

				//Close the previous postMessage connection.
				if (this.previewConnection) {
					this.previewConnection.disconnect();
					this.previewConnection = null;
				}

				const frame = this.$previewFrame.get(0) as HTMLIFrameElement;
				if (!frame || !(frame instanceof HTMLIFrameElement)) {
					return;
				}

				//Try to get the preview URL from the iframe.
				try {
					const url = frame.contentWindow.location.href;
					if (url) {
						this.currentPreviewUrl = url;
					}
				} catch (e) {
					//We can't get the URL directly, probably because it's a cross-origin iframe.
				}

				this.previewConnection = AmeAcCommunicator.connectToChild(
					frame,
					{
						'setPreviewUrl': (url: string) => {
							if (this.isPreviewableUrl(url)) {
								this.previewUrl = url;
							}
						},
						'notifyPreviewUrlChanged': (url: string) => {
							this.currentPreviewUrl = url;
						}
					},
					this.allowedCommOrigins
				);

				this.previewConnection.promise.then((connection) => {
					connection.execute('getCurrentUrl').then((url) => {
						if (url && (typeof url === 'string')) {
							this.currentPreviewUrl = url;
						}
					});
				});
			});

			this.previewUrl = this.initialPreviewUrl;

			//Notify other scripts. This lets them register custom controls and so on.
			$('#ame-ac-admin-customizer').trigger('adminMenuEditor:acRegister', [this]);

			const throttledReloadPreview = _.throttle(
				() => {
					this.queuePreviewFrameReload();
				},
				1000, //The reload method does its own throttling, so we use a low wait time here.
				{leading: true, trailing: true}
			);

			//Refresh the preview when any setting changes.
			this.settings.addChangeListener((setting, newValue) => {
				if (
					setting.supportsPostMessage
					&& this.previewConnection
					&& this.previewConnection.isConnected
				) {
					this.previewConnection.execute('previewSetting', setting.id, newValue);
				} else {
					throttledReloadPreview();
				}
			});
		}

		getSettingObservable(settingId: string, defaultValue: unknown): KnockoutObservable<any> {
			//Let's just implement this temporarily while working on refactoring this
			//stuff to use KO components.
			return this.settings
				.get(settingId)
				.map(setting => setting.value)
				.getOrElse(ko.observable(defaultValue));
		}

		getAllSettingValues(): Record<string, any> {
			throw new Error('Method not implemented.');
		}

		get previewUrl(): string | null {
			return this.currentPreviewUrl;
		}

		set previewUrl(url: string) {
			if (url === this.currentPreviewUrl) {
				return;
			}

			if (this.isPreviewableUrl(url)) {
				this.navigatePreviewFrame(url);
			}
		}

		private navigatePreviewFrame(url: string | null = null, forceReload: boolean = false) {
			const oldUrl = this.previewUrl;
			if (url === null) {
				url = oldUrl ?? this.initialPreviewUrl;
			}

			const isSameUrl = (oldUrl === url);
			if (isSameUrl && !forceReload) {
				return;
			}

			//If there are any unsaved changes, let's include them in the preview by simulating
			//a form submission and sending the changes as form data. The server-side component
			//will merge these changes with existing changeset data.
			const unsavedChanges = this.settings.unsavedChanges;
			const simulateFormSubmission = !_.isEmpty(unsavedChanges);

			const parsedUrl = new URL(url);

			//If we're not using form submission, add a special parameter
			//to the URL to force a refresh.
			const refreshParam = '_ame-ac-refresh-trigger';
			if (isSameUrl && !simulateFormSubmission) {
				parsedUrl.searchParams.set(refreshParam, Date.now() + '_' + Math.random());
			} else {
				//Otherwise, remove the parameter just to be safe.
				parsedUrl.searchParams.delete(refreshParam);
			}

			//Ensure that the changeset used in the preview matches the current
			//changeset and preview is enabled. This is just a precaution. Normally,
			//the preview script automatically changes link URLs.
			parsedUrl.searchParams.set('ame-ac-changeset', this.settings.changesetName());
			parsedUrl.searchParams.set('ame-ac-preview', '1');

			this.hasPendingPreviewReload = false; //Reloading now, so no longer pending.
			this.isFrameLoading = true;

			console.info('navigatePreviewFrame: Navigating to ' + parsedUrl.href);
			if (simulateFormSubmission) {
				const formData = {
					action: 'ws_ame_ac_refresh_preview_frame',
					"ame-ac-changeset": this.settings.changesetName(),
					modified: JSON.stringify(unsavedChanges),
					nonce: this.refreshPreviewNonce
				}

				const $form = $('<form>')
					.attr('method', 'post')
					.attr('action', parsedUrl.href)
					.attr('target', 'ame-ac-preview-frame')
					.appendTo('body');

				for (const key in formData) {
					const value = formData[key];
					$('<input>')
						.attr('type', 'hidden')
						.attr('name', key)
						.val(value)
						.appendTo($form);
				}

				this.currentPreviewUrl = parsedUrl.href;
				$form.trigger('submit');
				$form.remove();
			} else {
				this.currentPreviewUrl = parsedUrl.href;
				this.$previewFrame.attr('src', this.currentPreviewUrl);
			}
		}

		private _isFrameLoading: boolean = false;
		private frameLoadingTimeoutId: number = null;
		private lastPreviewLoadTimestamp: Date = new Date(0);

		private reloadWaitTimeoutId: number = null;
		private hasPendingPreviewReload: boolean = false;

		private set isFrameLoading(isLoading: boolean) {
			const wasLoadingBefore = this._isFrameLoading;
			if (!isLoading && (isLoading === wasLoadingBefore)) {
				return;
			}
			//In some circumstances, we may start to load a new URL before
			//the previous one has finished loading. This is valid and should
			//reset the load timeout.

			$('#ame-ac-preview-refresh-indicator').toggleClass('ame-ac-show-indicator', isLoading);
			if (this.frameLoadingTimeoutId) {
				clearTimeout(this.frameLoadingTimeoutId);
				this.frameLoadingTimeoutId = null;
			}

			if (isLoading) {
				//As a precaution, we'll assume that if the frame doesn't load in a reasonable
				//time, it will never finish loading.
				this.frameLoadingTimeoutId = window.setTimeout(() => {
					if (this.isFrameLoading) {
						this.isFrameLoading = false;
					}
				}, 20000);
			}
			this._isFrameLoading = isLoading;

			if (wasLoadingBefore && !isLoading) {
				this.lastPreviewLoadTimestamp = new Date();
			}

			//Once the frame is loaded, trigger any pending reload.
			if (!isLoading && this.hasPendingPreviewReload) {
				this.hasPendingPreviewReload = false;
				this.queuePreviewFrameReload();
			}
		}

		public get isFrameLoading(): boolean {
			return this._isFrameLoading;
		}

		private queuePreviewFrameReload() {
			if (this.reloadWaitTimeoutId) {
				return; //The frame will reload soon.
			}

			if (this.isFrameLoading) {
				this.hasPendingPreviewReload = true;
				return;
			}

			//To avoid stressing the server, wait at least X ms after the last
			//load completes before reloading the frame.
			const reloadWaitTime = 2000;
			const now = new Date();
			const timeSinceLastLoad = now.getTime() - this.lastPreviewLoadTimestamp.getTime();
			if (timeSinceLastLoad < reloadWaitTime) {
				this.reloadWaitTimeoutId = window.setTimeout(() => {
					this.reloadWaitTimeoutId = null;
					this.queuePreviewFrameReload();
				}, reloadWaitTime - timeSinceLastLoad);
				return;
			}

			//Actually reload the frame.
			this.navigatePreviewFrame(null, true);
		}

		navigateToRootSection() {
			this.sectionNavigation.navigateToSection('ame-ac-section-structure-root');
		}
	}
}

jQuery(function () {
	//Give other scripts a chance to load before we start.
	//Some of them also use jQuery to run when the DOM is ready.
	setTimeout(() => {
		window['wsAdminCustomizer'] = new AmeAdminCustomizer.AdminCustomizer(wsAmeAdminCustomizerData);

		ko.applyBindings(
			window['wsAdminCustomizer'],
			document.getElementById('ame-ac-admin-customizer')
		);

		//Navigate to the root section. In the current implementation this can't happen
		//until bindings have been applied, so it's not part of the constructor.
		setTimeout(() => {
			window['wsAdminCustomizer'].navigateToRootSection();
		}, 5); //Components are rendered asynchronously.
	}, 20);
});
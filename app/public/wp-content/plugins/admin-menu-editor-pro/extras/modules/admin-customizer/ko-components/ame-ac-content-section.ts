import {AmeAcSection} from './ame-ac-section.js';
import {createComponentConfig} from '../../../pro-customizables/ko-components/control-base.js';

class AmeAcContentSection extends AmeAcSection {
	constructor(params, $element) {
		super(params, $element);
	}
}

export default createComponentConfig(AmeAcContentSection, `
	<li class="ame-ac-control ame-ac-content-section">
		<h4 class="ame-ac-control-label ame-ac-content-section-title" data-bind="text: title"></h4>	
	</li>	
	<!-- ko foreach: childComponents -->
		<!-- ko component: $data --><!-- /ko -->
	<!-- /ko -->	
`);
import writeStyle from "../../../css-utilities/write-style";
import writeStyles from "../../../css-utilities/write-styles";
import writeBgPositionStyle from "../../../css-utilities/write-bg-position-style";
import writeBgSizeStyle from "../../../css-utilities/write-bg-size-style";

declare var wp: any;

const listenFormPositionFieldChange = () => {
	wp.customize("udb_login[form_position]", function (setting) {
		const formPositionStyleTag = document.querySelector(
			'[data-listen-value="udb_login[form_position]"]'
		) as HTMLStyleElement;

		const formBgColorStyleTag = document.querySelector(
			'[data-listen-value="udb_login[form_bg_color]"]'
		) as HTMLStyleElement;

		const formBgImageStyleTag = document.querySelector(
			'[data-listen-value="udb_login[form_bg_image]"]'
		) as HTMLStyleElement;

		const formBgRepeatStyleTag = document.querySelector(
			'[data-listen-value="udb_login[form_bg_repeat]"]'
		) as HTMLStyleElement;

		const formBgPositionStyleTag = document.querySelector(
			'[data-listen-value="udb_login[form_bg_position]"]'
		) as HTMLStyleElement;

		const formBgSizeStyleTag = document.querySelector(
			'[data-listen-value="udb_login[form_bg_size]"]'
		) as HTMLStyleElement;

		const formWidthStyleTag = document.querySelector(
			'[data-listen-value="udb_login[form_width]"]'
		) as HTMLStyleElement;

		const formHorizontalPaddingStyleTag = document.querySelector(
			'[data-listen-value="udb_login[form_horizontal_padding]"]'
		) as HTMLStyleElement;

		const formBorderWidthStyleTag = document.querySelector(
			'[data-listen-value="udb_login[form_border_width]"]'
		) as HTMLStyleElement;

		setting.bind(function (val) {
			let formBgColor = wp.customize("udb_login[form_bg_color]").get();
			const formBgImage = wp.customize("udb_login[form_bg_image]").get();
			const formBgRepeat = wp.customize("udb_login[form_bg_repeat]").get();
			const formBgPosition = wp.customize("udb_login[form_bg_position]").get();
			const formBgSize = wp.customize("udb_login[form_bg_size]").get();
			let boxWidth = wp.customize("udb_login[box_width]").get();
			let formWidth = wp.customize("udb_login[form_width]").get();

			let formHorizontalPadding = wp
				.customize("udb_login[form_horizontal_padding]")
				.get();

			formBgColor = formBgColor ? formBgColor : "#ffffff";
			boxWidth = boxWidth ? boxWidth : "40%";
			formWidth = formWidth ? formWidth : "320px";

			formHorizontalPadding = formHorizontalPadding
				? formHorizontalPadding
				: "24px";

			// The "default" value is handled in the free version.
			if (val !== "default") {
				if (val === "left") {
					formPositionStyleTag.innerHTML =
						"#login {margin-right: auto; margin-left: 0; min-width: 320px; width: " +
						boxWidth +
						"; min-height: 100%;} #loginform {max-width: " +
						formWidth +
						"; box-shadow: none;}";
				} else if (val === "right") {
					formPositionStyleTag.innerHTML =
						"#login {margin-right: 0; margin-left: auto; min-width: 320px; width: " +
						boxWidth +
						"; min-height: 100%;} #loginform {max-width: " +
						formWidth +
						"; box-shadow: none;}";
				}

				if (formBgColor) {
					writeStyles({
						styleEl: formBgColorStyleTag,
						styles: [
							{
								cssSelector: "#login",
								cssRules: "background-color: " + formBgColor + ";",
							},
							{
								cssSelector: ".login form, #loginform",
								cssRules: "background-color: transparent;",
							},
						],
					});
				}

				if (formBgImage) {
					writeStyles({
						styleEl: formBgImageStyleTag,
						styles: [
							{
								cssSelector: "#login",
								cssRules: "background-image: url(" + formBgImage + ");",
							},
							{
								cssSelector: ".login form, #loginform",
								cssRules: "background-image: none;",
							},
						],
					});
				}

				if (formBgRepeat) {
					writeStyle({
						styleEl: formBgRepeatStyleTag,
						cssSelector: "#login",
						cssRules: "background-repeat: " + formBgRepeat + ";",
					});
				}

				if (formBgPosition) {
					writeBgPositionStyle({
						styleEl: formBgPositionStyleTag,
						keyPrefix: "form_",
						cssSelector: "#login",
						bgPosition: formBgPosition,
					});
				}

				if (formBgSize) {
					writeBgSizeStyle({
						styleEl: formBgSizeStyleTag,
						keyPrefix: "form_",
						cssSelector: "#login",
						bgSize: formBgSize,
					});
				}

				formWidthStyleTag.innerHTML = formWidthStyleTag.innerHTML.replace(
					"#login {width:",
					"#loginform {max-width:"
				);

				formHorizontalPaddingStyleTag.innerHTML =
					"#loginform {padding-left: 24px; padding-right: 24px;}";

				formBorderWidthStyleTag.innerHTML = "#loginform {border-width: 0;}";
			}
		});
	});
};

export default listenFormPositionFieldChange;

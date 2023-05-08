declare var wp: any;

const listenLayoutSectionState = () => {
	wp.customize.section(
		"udb_login_customizer_layout_section",
		function (section: any) {
			section.expanded.bind(function (isExpanded: boolean | number) {
				if (isExpanded) {
					// The rest of "default" is handled in the free version.
					if (wp.customize("udb_login[form_position]").get() === "default") {
						wp.customize.control("udb_login[box_width]").deactivate();
					} else {
						wp.customize.control("udb_login[box_width]").activate();

						wp.customize
							.control("udb_login[form_horizontal_padding]")
							.deactivate();

						wp.customize.control("udb_login[form_border_width]").deactivate();
						wp.customize.control("udb_login[form_border_style]").deactivate();
						wp.customize.control("udb_login[form_border_color]").deactivate();

						wp.customize.control("udb_login[form_border_radius]").deactivate();

						wp.customize.control("udb_login[enable_form_shadow]").deactivate();

						wp.customize.control("udb_login[form_shadow_blur]").deactivate();
						wp.customize.control("udb_login[form_shadow_color]").deactivate();
					}
				}
			});
		}
	);
};

export default listenLayoutSectionState;

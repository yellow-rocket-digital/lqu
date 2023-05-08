<style>
input[name="mishaDateFrom"], input[name="mishaDateTo"]{
	line-height: 28px;
	height: 28px;
	margin: 0;
	width:125px;
}
</style>
<div id="wedevs-project-manager" class="wedevs-pm-wrap wrap wp-core-ui pm pm-page-wrapper">
	<h1>Download Task Report</h1>
	<form method="post" action="/wp-admin/admin.php?page=project-report">
		<input type="hidden" name="download_reports" value="yes">
		<?php wp_nonce_field( 'download_reports_nonce'); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="default_category">Associated User</label></th>
					<td>
						<select name="user" id="user_select" style="width: 200px" class="user_select">
							<option class="level-0" value="">Any User</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="default_post_format">Created Date</label></th>
					<td>		
						<input type="text" name="from" placeholder="Date From" value="" />
						<input type="text" name="to" placeholder="Date To" value="" />
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Download"></p>
	</form>
</div>

<script>
jQuery( function($) {
	var from = $('input[name="from"]'),
	    to = $('input[name="to"]');

		from.datepicker( {dateFormat : "yy-mm-dd", numberOfMonths: 2, changeMonth: true} ).on( "change", function() {
          to.datepicker( "option", "minDate", getDate( this ) );
        });
		to.datepicker( {dateFormat : "yy-mm-dd", numberOfMonths: 2, changeMonth: true} )
	      .on( "change", function() {
	        from.datepicker( "option", "maxDate", getDate( this ) );
	      });
	    function getDate( element ) {
	      var date;
	      try {
	        date = $.datepicker.parseDate( "yy-mm-dd", element.value );
	      } catch( error ) {
	        date = new Date();
	      }
	 
	      return date;
	    }
	 jQuery('.user_select').select2({
        ajax: {
            url: window.ajaxurl,
            data: function(params) {
                var query = {
                    action: 'pfd_users_list',
                    q: params.term,
                    page: params.page || 1
                }
                return query;
            }
        }
    });
});
</script>
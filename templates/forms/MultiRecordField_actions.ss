<% if $Actions %>
	<div class="multirecordfield-actions <% if $IsBefore %>multirecordfield-actions-before<% else %>multirecordfield-actions-after<% end_if %> js-multirecordfield-actions clearfix">
		$Actions
		<div class="multirecordfield-loading js-multirecordfield-loading"></div>
	</div>
<% end_if %>
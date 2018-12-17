<% if $Actions %>
	<div class="multirecordfield-actions <% if $IsAfter == 1 %>multirecordfield-actions-after<% else %>multirecordfield-actions-before<% end_if %> js-multirecordfield-actions clearfix">
		$Actions
		<div class="multirecordfield-loading js-multirecordfield-loading"></div>
	</div>
<% end_if %>

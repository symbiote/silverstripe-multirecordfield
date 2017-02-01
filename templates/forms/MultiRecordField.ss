<div class="field js-multirecordfield-field {$ExtraClass}">
	<div class="clear"></div>
	<div class="multirecordfield-errors-holder">
		<%-- JavaScript errors get printed here --%>
		<p class="multirecordfield-errors js-multirecordfield-errors"></p>
	</div>
	<div class="multirecordfield-deleted js-multirecordfield-deleted-list" style="display: none;">
		<%-- Deleted fields get stored here as an <input> to track what got deleted --%>
	</div>
	<% include MultiRecordField_actions IsAfter=0 %>
	<div class="multirecordfield-fields <% if not $Fields %>is-empty<% end_if %> js-multirecordfield-list">
		<% if $Fields %>
			$Fields
		<% end_if %>
	</div>
	<% include MultiRecordField_actions IsAfter=1 %>
	<div class="clear"></div>
</div>
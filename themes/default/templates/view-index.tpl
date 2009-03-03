{include file='header.tpl'}

{if $userGroups}
<p>User Groups: {$userGroups}</p>
{/if} 
{if $department}
<p>
    <div>Department: {$department}</div>
    <div>Position: {$position}</div>
</p>
{/if} 

{include file='footer.tpl'}

{include file='pogo:header.tpl'}
{if $user}
    <a href="{action controller="login" action="takeLogout"}">Logout</a>
{else}
    <a href="{action controller="login" action="login"}">Login</a>
    <a href="{action controller="login" action="signup"}">Signup</a>
{/if}
{include file='pogo:footer.tpl'}
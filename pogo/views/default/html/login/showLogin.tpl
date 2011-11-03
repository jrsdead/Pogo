{include file='pogo:header.tpl'}
<h1>Login</h1>
<form method="post">
	<input type="hidden" name="controller" value="login" />
	<input type="hidden" name="action" value="takeLogin" />
{if $origin}
	<input type="hidden" name="origin" value="{$origin}" />
{/if}
	User: <input type="text" name="user" /><br />
	Pass: <input type="password" name="pass" /><br />
	<input type="submit" value="Login" />
</form>
{include file='pogo:footer.tpl'}
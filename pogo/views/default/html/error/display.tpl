{include file='pogo:header.tpl'}
<h1>Error</h1>
{$message}<br />
{if $internalError}
<br /><br />
Error {$internalError->getErrStr()}<br />
File: {$internalError->getErrFile()} (line {$internalError->getErrLine()})<br />
<pre>{var_export($internalError->getErrStack())}</pre>
{/if}
{include file='pogo:footer.tpl'}
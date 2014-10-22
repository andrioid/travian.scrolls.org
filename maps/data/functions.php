<?php

function fromCache($key) {
	if (function_exists('apc_fetch') and ($result = @apc_fetch($key))) {
		return $result;
	}
	return false;
}

function toCache($key, $data, $ttl=-1) {
	global $Config;
	if (!function_exists('apc_store')) { return false; }
	if ($ttl == -1) { $ttl = 60*60*12; }
	if (!@apc_store($key, $data, $ttl)) { return false; }
	return true;
}

?>

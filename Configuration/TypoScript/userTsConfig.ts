[globalVar = GP:interface = frontend]
	# Send a "fake" param so first page wont be fetched from cache
	auth.BE.redirectToURL = ../?redirectToFrontend=1
[else]
	auth.BE.redirectToURL = backend.php
[global]
# uesp-lighttpd404thumbnail
A very basic script used on the UESP.net to intercept 404 events from Lighttpd thumbnail loads and dynamically create the desired size thumbnail if possible. 

To install simply add a line similar to the following in your Lighttpd config:

       server.error-handler-404 = "/lighttpd404thumbnail.php"

Is not intended for use outside of the UESP but should work in any MediaWiki installation. Edit the $BASE_PATH variable to point to your wiki images folder.

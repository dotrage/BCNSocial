<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>BarCamp Nashville Social:  Connecting BCN10 sessions to the people you know</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <link rel="stylesheet" href="/style/fp.css" type="text/css" title="">
    <script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="/js/jquery-ui-1.8.2.custom.min.js"></script>    
    <script>
    	var m = "<?php echo $id; ?>";
    	var u = "<?php echo $sig; ?>";
    </script> 
    <script type="text/javascript" src="/js/fp.js"></script>
	<script type="text/javascript">
	
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-9941248-2']);
	  _gaq.push(['_setDomainName', '.friendspanels.com']);
	  _gaq.push(['_trackPageview']);
	
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	
	</script>      
  </head>
  <body>
	<div id="frame">
		<div id="header"></div>
<?php 
	if (isset($userview)){
		echo "<div id=\"header-right\">
			Add Your Favorite Sessions to BCN Social!
		</div>";
	}
	else if (!isset($mobile)){
		echo "<div id=\"header-register\">
			If you haven't done so, you need to register<br>
			to attend BCN on the official BCN Site.
		</div>";	
	}
?>		

		<div style="clear: both"></div>
		<?php echo $page_content; ?>		
		<div id="footer">AN 8 HOUR CONSPIRACY BY <a href="http://twitter.com/chrisennis" target="_blank">CHRISENNIS</a> & <a href="http://functionized.com" target="_blank">FUNCTION INTERACTIVE</a></div>
	</div>
 
 </body>
</html>

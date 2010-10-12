<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<meta name="google-site-verification" content="kHMgSWEccOyc830xGuuTe9MGLRPoPbBaaR7FS17ROIo" />		
		<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
		<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
		<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
		<?php wp_head(); ?>
		<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery-1.3.2.min.js" ></script>
		<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery-ui-1.7.2.custom.min.js" ></script>

	    <script type="text/javascript">


	    var browserDetect = {
	    	init: function () {
	    		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
	    		this.version = this.searchVersion(navigator.userAgent)
	    			|| this.searchVersion(navigator.appVersion)
	    			|| "an unknown version";
	    		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	    	},
	    	searchString: function (data) {
	    		for (var i=0;i<data.length;i++)	{
	    			var dataString = data[i].string;
	    			var dataProp = data[i].prop;
	    			this.versionSearchString = data[i].versionSearch || data[i].identity;
	    			if (dataString) {
	    				if (dataString.indexOf(data[i].subString) != -1)
	    					return data[i].identity;
	    			}
	    			else if (dataProp)
	    				return data[i].identity;
	    		}
	    	},
	    	searchVersion: function (dataString) {
	    		var index = dataString.indexOf(this.versionSearchString);
	    		if (index == -1) return;
	    		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	    	},
	    	dataBrowser: [
	    		{
	    			string: navigator.userAgent,
	    			subString: "Chrome",
	    			identity: "Chrome"
	    		},
	    		{ 	string: navigator.userAgent,
	    			subString: "OmniWeb",
	    			versionSearch: "OmniWeb/",
	    			identity: "OmniWeb"
	    		},
	    		{
	    			string: navigator.vendor,
	    			subString: "Apple",
	    			identity: "Safari",
	    			versionSearch: "Version"
	    		},
	    		{
	    			prop: window.opera,
	    			identity: "Opera"
	    		},
	    		{
	    			string: navigator.vendor,
	    			subString: "iCab",
	    			identity: "iCab"
	    		},
	    		{
	    			string: navigator.vendor,
	    			subString: "KDE",
	    			identity: "Konqueror"
	    		},
	    		{
	    			string: navigator.userAgent,
	    			subString: "Firefox",
	    			identity: "Firefox"
	    		},
	    		{
	    			string: navigator.vendor,
	    			subString: "Camino",
	    			identity: "Camino"
	    		},
	    		{		// for newer Netscapes (6+)
	    			string: navigator.userAgent,
	    			subString: "Netscape",
	    			identity: "Netscape"
	    		},
	    		{
	    			string: navigator.userAgent,
	    			subString: "MSIE",
	    			identity: "Explorer",
	    			versionSearch: "MSIE"
	    		},
	    		{
	    			string: navigator.userAgent,
	    			subString: "Gecko",
	    			identity: "Mozilla",
	    			versionSearch: "rv"
	    		},
	    		{ 		// for older Netscapes (4-)
	    			string: navigator.userAgent,
	    			subString: "Mozilla",
	    			identity: "Netscape",
	    			versionSearch: "Mozilla"
	    		}
	    	],
	    	dataOS : [
	    		{
	    			string: navigator.platform,
	    			subString: "Win",
	    			identity: "Windows"
	    		},
	    		{
	    			string: navigator.platform,
	    			subString: "Mac",
	    			identity: "Mac"
	    		},
	    		{
	    			   string: navigator.userAgent,
	    			   subString: "iPhone",
	    			   identity: "iPhone/iPod"
	    	    },
	    		{
	    			string: navigator.platform,
	    			subString: "Linux",
	    			identity: "Linux"
	    		}
	    	]
	    };
browserDetect.init();

    
$(document).ready(function() {
	$("#MainHeader1_countrygo").click(function (){
		window.location.href = $("#MainHeader1_country").val();
	});

	$("#logo").click(function (){
		window.location.href = "/";
	});

	$("#MainHeader1_search").focus(function(){
		this.value="";
	});

	$("#MainHeader1_countrygo").click(function (){
		window.location.href = $("#MainHeader1_country").val();
	});

	$("#MainHeader1_searchgo").click(function (){
		window.location.href = "http://www.atosorigin.com/en-us/tools/search/results/default.htm?txtQT=" + $("#MainHeader1_search").val();
	});

	$("#subscribe_dialog").dialog(
			{ 
				draggable: false,
				modal: true,
				resizable: false,
				autoOpen: false
			}
	);
	if(browserDetect.browser == 'Explorer' && browserDetect.version == '6'){
		$("#rss_subscribe_link").click(
				function(event){
				event.preventDefault();
				$("#subscribe_dialog").dialog('open');
			}
		);
	};
	$("#blog_badge_dialog").dialog(
    		{ 
    			draggable: false,
    			modal: true,
    			resizable: false,
    			autoOpen: false
    		}
	);
	$("#blog_badge_link").click(
			function(event){
			event.preventDefault();
			$("#blog_badge_dialog").dialog('open');
		}
	);
		
});


	    
		</script>
	</head>
	<body>
		<!-- body class <?php body_class(); ?> -->
		<div id="subscribe_dialog" class="right_column_block" title="Subscribe to RSS feed" style="display:none;">
			add this url to your reader: <?php bloginfo('rss2_url'); ?>
		</div>

		<!--  begin page -->
		<div id="page">
		
			<!--  begin header -->
			<div id="header" role="banner">


				<div id="TopBanner">
					<div class="TopHeaderLeft" id="header1">
						<table cellspacing="0" cellpadding="0" border="0" width="100%" class="siteWide" id="MainHeader1_hdrRow1">
							<tbody>
								<tr>
									<td>
										<span id="hdrGloballink">
											<a href="/" class="hdrLink" title="Atos Origin Global Site homepage" id="MainHeader1_ao.com">Home</a>
										</span>
										
										<span id="hdrContactus">
											<a href="/contact-us/" title="Contact Us" id="MainHeader1_contactus">Contact Us</a>
										</span>
										<span id="hdrSearch">
											<?php get_search_form(); ?>
											<!-- 
											<input 
												type="text" 
													onkeypress="return fnSearchKey(event)" 
													onfocus="fnClearDefault(this)" 
													class="hdrSearchInput" 
													title="Enter your search criteria" 
													id="MainHeader1_search" 
													value="Enter Search" 
													name="MainHeader1:search"/>
											-->
										</span>
										<!--  
										<a href="javascript:void(0);" onclick="return fnSearchGo(false);" class="GoNewButtonSearch" title="Search www.atosorigin.com">
											<img border="0" alt="" src="<?php bloginfo('template_directory'); ?>/images/gobutton.gif" title="Search www.atosorigin.com" id="MainHeader1_searchgo"/>
										</a>
										-->
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="TopHeaderRight" id="aologo">
            			<img border="0" style="margin-top: 8px;" usemap="#aologoolympicsxml" src="<?php bloginfo('template_directory'); ?>/images/AtosOrigin_Olympic_Games_Logo.gif"/>
		                <map name="aologoolympicsxml">
		                    <area alt="Atos Origin Homepage" coords="0,0,108,89" shape="rect" href="http://www.atosorigin.com/en-us/"/>
		                    <area alt="Atos Origin Olympic Homepage" coords="109,0,216,89" shape="rect" href="http://www.atosorigin.com/en-us/olympic_games/"/>
		                </map>
		            </div>
				</div>
				
				<div class="NavBar">
		
					<div id="teaser">
						<object 
							classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" 
							codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" 
							width="930" 
							height="113" 
							id="main11" 
							align="middle">
							<param name="allowScriptAccess" value="sameDomain" />
							<param name="allowFullScreen" value="false" />
							<param name="movie" value="<?php bloginfo('template_directory'); ?>/animation/Animation.swf" />
							<param name="quality" value="high" />
							<param name="bgcolor" value="#333333" />	
							<embed src="<?php bloginfo('template_directory'); ?>/animation/Animation.swf" 
								quality="high" 
								bgcolor="#333333" 
								width="930" 
								height="113" 
								name="main11" 
								align="middle" 
								allowScriptAccess="sameDomain" 
								allowFullScreen="false" 
								type="application/x-shockwave-flash" 
								pluginspage="http://www.macromedia.com/go/getflashplayer" />
						</object>					
						<!-- 
						<?php bloginfo('name'); ?></a><br/>
						<?php bloginfo('description'); ?><br/>
						-->
					</div>					
					<div id="banner"></div>
					<div id="primary_navigation">
						<ul>
							<li class="home"><a href="<?php echo get_option('home'); ?>/">Home</a></li>
							<?php wp_list_pages('title_li=' ); ?>
						</ul>
					</div>
				</div>
			</div>

			<!-- end header -->
			
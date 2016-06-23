<?php

	// wp_cvssupport.php
	//
   // Allows for the import and export of comma seperated files (CVS/TEXT) 
   // handy for importing lists of keywords into Excel and vice versa
   // Change CVS to TEXT 2009/10/12  ############### New ##########
   
	function wp_keywordlink_cvsexportvalue($value,$islast=false)
	{
		if ($value) echo '1';
		else echo '0';
		if (!$islast) echo ',';
	}	   
   
   function wp_keywordlink_cvsexport()
	{
		$links = get_option(WP_KEYWORDLINK_OPTION);

		/* Tell the browser to expect an CVS file */
		header('Content-type: application/text');
		header('Content-Disposition: attachment; filename="wp_weywordlink.export"');

		/* Generate the header line */
		echo "Keyword,URL,NoFollow,First Only,New Window,Ignore Case,IsAffiliate,Enable In Comments,Chinese Keyword,Description\n";

	 	foreach ($links as $keyword => $details)
		{
			   list($link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN,$desc) = explode("|",$details);
				$cleankeyword = $keyword;
				if(!$desc){ $desc = $cleankeyword; }
				echo "\"$cleankeyword\",";
				echo "\"$link\",";
				
				wp_keywordlink_cvsexportvalue($nofollow,false);
				wp_keywordlink_cvsexportvalue($firstonly,false);
				wp_keywordlink_cvsexportvalue($newwindow,false);
				wp_keywordlink_cvsexportvalue($ignorecase,false);
				wp_keywordlink_cvsexportvalue($isaffiliate,false);
				wp_keywordlink_cvsexportvalue($docomments,false);
				wp_keywordlink_cvsexportvalue($zh_CN,true);

				echo ",\"$desc\"";
				echo "\n";								 				
		}

		/* End of the show */		
		die(0);
	} 
	
	function wp_keywordlink_cvsmenu()
	{
		?>
		<h3><?php _e('Import and Export File','wp_keywordlink');?></h3>
		<p><?php _e('To allow for easy of editing of your keywords in a spreadsheet you can save to and from a comma seperatated values (CVS/TEXT) file.','wp_keywordlink');?></p>
		<form enctype="multipart/form-data" name=cvs_form method="post" action="">
		 <input type="radio" name="action" value="exportcvs" checked /><?php _e('Export to File','wp_keywordlink');?>
		 <input type="submit" value="Submit"><BR/>
		 <input type="radio" name="action" value="importcvs" /><?php _e('Import from file','wp_keywordlink');?>
		 <input type="file"  name="upload" />
		 <input type="submit" value="Submit">		 
		 <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
		</form>
		<p><?php _e('Example file:','wp_keywordlink');?> <a href="<?php echo wp_keywordlink_GetPluginUrl();?>/wp_weywordlink.import" target="_blank"><?php _e('Save As','wp_keywordlink');?></a></p>
		<?php
	}


	function wp_keywordlink_cvsimport()
	{

		if (is_uploaded_file($_FILES['upload']['tmp_name'])) 
		{
			$cvscontent = file($_FILES['upload']['tmp_name'],FILE_IGNORE_NEW_LINES);
			$links = get_option(WP_KEYWORDLINK_OPTION);	

			// Keep some statistics
			$cnt = 0; $skip = 0; $replace = 0; $added = 0;
			 
			foreach($cvscontent as $row => $value)
			{
				// Skip the first row 
				if ($cnt++ == 0) 
				{	
					// A little check to see if the file we are importing isn't complete garbage
					if (strstr($value,"Keyword")===FALSE)
					{
			 			wp_keywordlink_topbarmessage("Not a Valid File! Please Cheack File Format");
			 			return;
					}
					continue;
				}
				if (preg_match("/^#/",$value) )
				{
				  $skip++;
				  continue;
				}
				
				list($keyword,$link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN,$desc) = explode(",",$value);
				
				// Strip "" from the beginning and end of the keyword and url
				$keyword = trim($keyword, "\"");
				$link    = trim($link, "\"");
				if(!$desc){ $desc = $keyword; } else { $desc    = trim($desc, "\""); }
				// Ignore empty keywords, or keywords with no link 
				if ($keyword == "" || $link == "")
				{
				  $skip++;
				  continue;
				}

				// Count how many entries we are replacing				
  				if ($links[$keyword]) $replace++; else $added++;
				
				// Input validation
				if ($nofollow) $nofollow = 1; 
				if ($firstonly) $firstonly = 1;
				if ($newwindow) $newwindow = 1;
				if ($ignorecase) $ignorecase = 1;
				if ($isaffiliate) $isaffiliate = 1;
				if ($docomments) $docomments = 1;
				if ($zh_CN) $zh_CN = 1;
				
	  			$newlinks[$keyword] = implode('|',array($link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN,$desc));
			}
			
			// If we encountered no errors, merge the new keywords with the existing keywords
			foreach($newlinks as $keyword => $parameters)
			 $links[$keyword] = $parameters;
			 
			// Update the wordpress database
			update_option(WP_KEYWORDLINK_OPTION,$links);      
			
 			wp_keywordlink_topbarmessage("Import complete (replaced $replace keywords, ignored $skip empty, added $added entries)");			

		}
		else
 			wp_keywordlink_topbarmessage("Error uploading file");
 
	}
	
	/* wp_keywordlink_checkcvs 
	 *
    * Tied to the 'init' action to ensure it runs before any headers are sent
   */
	function wp_keywordlink_checkcvs()
	{
		if(isset($_POST['action'])) { if ($_POST['action']=='exportcvs')
		 wp_keywordlink_cvsexport();		
	}}

      
?>
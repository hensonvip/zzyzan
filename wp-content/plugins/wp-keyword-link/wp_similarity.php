<?php
# 2.6.2.0
function wp_sim_by_tag() {
	$list = wp_get_list("tag");
	echo wp_print_similarity($list);
	echo '<!-- Similarity - Sim_by_Tag -->';
}
function wp_sim_by_cat() {
	$list = wp_get_list("cat");
	echo wp_print_similarity($list);
	echo '<!-- Similarity - Sim_by_Cat -->';
}
function wp_sim_by_mix_multi() {
	$taglist = wp_get_list("firstt");
	$catlist = wp_get_list("firstc");
	$list = wp_mix_lists($taglist, $catlist);
	echo wp_print_similarity($list);
	echo '<!-- Similarity - Sim_by_Mix -->';
}
function wp_sim_by_tag_multi() {
	$list = wp_get_list("firstt");
	echo wp_print_similarity($list);
	echo '<!-- Similarity - Sim_by_Tag -->';
}
function wp_sim_by_cat_multi() {
	$list = wp_get_list("firstc");
	echo wp_print_similarity($list);
	echo '<!-- Similarity - Sim_by_Cat -->';
}
function wp_sim_by_mix() {
	$taglist = wp_get_list("tag");
	$catlist = wp_get_list("cat");
	$list = wp_mix_lists($taglist, $catlist);
	echo wp_print_similarity($list);
	echo '<!-- Similarity - Sim_by_Mix -->';
}

function wp_auto_display($content) {
	global $wpdb, $post;
	$options = get_option(basename(__FILE__, ".php"));
	$adf = stripslashes($options['adf']);
	$sim_pages = stripslashes($options['sim_pages']);
	if ($sim_pages != 'yes') $sim_pages = 'no';
	$sim_rss = stripslashes($options['sim_rss']);
	if ($sim_rss != 'yes') $sim_rss = 'no';

	if ($sim_rss == 'no' && is_feed())
		return $content;
	elseif ( (is_single() or (is_page() and $sim_pages == 'yes') or (is_feed() and $sim_rss == 'yes') ) and $adf != 'none') 
		switch ($adf) {
		case 'tag':
			$list = wp_get_list("tag");
			return $content.'<div class="similarity">'.wp_print_similarity($list).'</div><!-- Tag -->';
			break;
		case 'cat':
			$list = wp_get_list("cat");
			return $content.'<div class="similarity">'.wp_print_similarity($list).'</div><!-- Cat -->';
			break;
		case 'mix':
			$taglist = wp_get_list("tag");
			$catlist = wp_get_list("cat");
			$list = wp_mix_lists($taglist, $catlist);
			return $content.'<div class="similarity">'.wp_print_similarity($list, 'auto').'</div><!-- Mix -->';
			break;
		default:
			return $content;
		}
	else
		return $content;
}

function wp_short_tag() {
	global $wpdb, $post;
	if (is_single()) wp_sim_by_tag();
}
function wp_short_cat() {
	global $wpdb, $post;
	if (is_single()) wp_sim_by_cat();
}
function wp_short_mix() {
	global $wpdb, $post;
	if (is_single()) wp_sim_by_mix();
}

function wp_print_similarity($list, $template = 'default') {
	global $current_user;
	$options = get_option(basename(__FILE__, ".php"));
	$limit = stripslashes($options['limit']);
	$none_text = stripslashes($options['none_text']);
	if ($template == 'auto') {
		$prefix = stripslashes($options['auto_prefix']);
		$suffix = stripslashes($options['auto_suffix']);
		$format = stripslashes($options['auto_format']);
		$output_template = stripslashes($options['auto_output_template']);
	} else {
		$prefix = stripslashes($options['prefix']);
		$suffix = stripslashes($options['suffix']);
		$format = stripslashes($options['format']);
		$output_template = stripslashes($options['output_template']);
	}
	$minimum_strength = stripslashes($options['minimum_strength']);
	$random_min = stripslashes($options['random_min']);
	// an empty output_template makes no sense so we fall back to the default
	if ($output_template == '') $output_template = '<li>{link} ({strength})</li>';
	$output = '';
	$output .= $prefix;
	if (sizeof($list) < 1) {
		$output = $none_text;
	} else {
		if ($limit < 0 || $limit > sizeof($list)) {
			$limit = sizeof($list);
		}
		$returnable = 'false';
		for ($i = 0; $i < $limit; $i++) {
			if ($minimum_strength > $list[$i]['strength']) {
				$i = $limit;
			} else {
				$post = get_post($list[$i]['post_id']);
				switch ($post->post_status) {
				case 'private':
					$show = 'false';
					if (($current_user->ID == $post->post_author)
					|| ($current_user->has_cap('read_private_posts')))  { // Author and those with capability
						$show = 'true';
						$returnable = 'true';
					}
					break;
				case 'draft': //unpublished posts
				case 'pending':
					$show = 'false';
					if ($current_user->ID == $post->post_author) { // Author only
						$show = 'true';
						$returnable = 'true';
					}
					break;
				case 'publish': // show non-private posts to anyone
					$show = 'true';
					$returnable = 'true';
					break;
				default: //non-posts (such as images) picked up by the query (who knew)
					$show = 'false';
					break;
				}
				if ($show == 'true') {
					switch ($format)
					{
					case 'percent':
						$list[$i]['strength'] = ($list[$i]['strength'] * 100) . '%';
						break;  
					case 'text':
						if ($list[$i]['strength'] > 0.75) {
							$list[$i]['strength'] = stripslashes($options['text_strong']);
						} elseif ($list[$i]['strength'] > 0.5) {
							$list[$i]['strength'] = stripslashes($options['text_mild']);
						} elseif ($list[$i]['strength'] > 0.25) {
							$list[$i]['strength'] = stripslashes($options['text_weak']);
						} else {
							$list[$i]['strength'] = stripslashes($options['text_tenuous']);
						}
						break;  
					case 'color':
						$r = 255;
						$g = 255;
						if ($list[$i]['strength'] > 0.5) {
							$r = 255 * (.5 - ($list[$i]['strength'] - .5));
						} elseif ($list[$i]['strength'] < 0.5) {
							$g = 513 * $list[$i]['strength'];
						}
						$shade = 'rgb('.number_format($r).', '.number_format($g).', 0)';
						$list[$i]['strength'] = '<span style="background-color: '.$shade.'; border: #000 1px solid">&nbsp;&nbsp;&nbsp;</span>';
						break;
					default:
						break;
					}
					$impression = str_replace("{title}",$post->post_title,str_replace("{url}",get_permalink($list[$i]['post_id']),str_replace("{strength}",$list[$i]['strength'],str_replace("{link}","<a href=\"{url}\">{title}</a>",$output_template))));
					$output .= $impression;
				} else {
					if ($limit < sizeof($list)) {
						$limit++;
					}
				}
			}
		}
		if (($limit < sizeof($list)) && (stripslashes($options['one_extra']) == 'true')) {
			$show = 'false';
			$try = 0;
			while (($show =='false') && ($try < 100)) {
				srand ((double) microtime( )*1000000);
				$i = rand($limit + 1,sizeof($list));
				$post = get_post($list[$i]['post_id']);
				switch ($post->post_status) {
				case 'private':
					$show = 'false';
					if (($current_user->ID == $post->post_author)
					|| ($current_user->has_cap('read_private_posts')))  {
						if ($random_min < $list[$i]['strength']) {
							$show = 'true';
							$returnable = 'true';
						}
					}
					break;
				case 'draft':
				case 'pending':
					$show = 'false';
					if ($current_user->ID == $post->post_author) {
						if ($random_min < $list[$i]['strength']) {
							$show = 'true';
							$returnable = 'true';
						}
					}
					break;
				case 'publish': // show non-private posts to anyone
					if ($random_min < $list[$i]['strength']) {
						$show = 'true';
						$returnable = 'true';
					} else {
						$show = 'false';
					}
					break;
				default: 
					$show = 'false';
					break;
				}
				if ($show == 'true') {
					switch ($format)
					{
					case 'percent':
						$list[$i]['strength'] = __('RANDOM', 'wp_keywordlink') . ' - ' . ($list[$i]['strength'] * 100) . '%';
						break;  
					case 'text':
						if ($list[$i]['strength'] > 0.75) {
							$list[$i]['strength'] = __('RANDOM', 'wp_keywordlink') . ' - ' . stripslashes($options['text_strong']);
						} elseif ($list[$i]['strength'] > 0.5) {
							$list[$i]['strength'] = __('RANDOM', 'wp_keywordlink') . ' - ' . stripslashes($options['text_mild']);
						} elseif ($list[$i]['strength'] > 0.25) {
							$list[$i]['strength'] = __('RANDOM', 'wp_keywordlink') . ' - ' . stripslashes($options['text_weak']);
						} else {
							$list[$i]['strength'] = __('RANDOM', 'wp_keywordlink') . ' - ' . stripslashes($options['text_tenuous']);
						}
						break;  
					case 'color':
						$r = 255;
						$g = 255;
						if ($list[$i]['strength'] > 0.5) {
							$r = 255 * (.5 - ($list[$i]['strength'] - .5));
						} elseif ($list[$i]['strength'] < 0.5) {
							$g = 513 * $list[$i]['strength'];
						}
						$shade = 'rgb('.number_format($r).', '.number_format($g).', 0)';
						$list[$i]['strength'] = '<span style="background-color: '.$shade.'; border: #000 1px solid">' . __('RANDOM', 'wp_keywordlink') . '</span>';
						break;
					default:
						$list[$i]['strength'] = __('RANDOM', 'wp_keywordlink') . ' - ' . $list[$i]['strength'];
						break;
					}
					$impression = str_replace("{title}",$post->post_title,str_replace("{url}",get_permalink($list[$i]['post_id']),str_replace("{strength}",$list[$i]['strength'],str_replace("{link}","<a href=\"{url}\">{title}</a>",$output_template))));
					$output .= $impression;
				} else { $try++; }
			}
		}

	}
	if ($returnable == 'true') {
		$output .= $suffix;
	} else {
		$output = $none_text;
	}
	return $output;
}

function wp_get_list($type = 'tag') {
	global $post, $wpdb, $wp_version;
	switch ($type) {
	case "firstc":
		if (have_posts()) the_post();
		$type = 'cat';
		break;
	case "firstt":
		if (have_posts()) the_post();
		$type = 'tag';
		break;
	}
	$list = array();
	$id_list = array();
	$strength_list = array();
	$potential = 0;
	$query = "select r.term_taxonomy_id as ttid, t.count as rarity, rand() as mix from $wpdb->term_relationships r, $wpdb->term_taxonomy t where r.object_id = '$post->ID' and r.term_taxonomy_id in (select term_taxonomy_id from $wpdb->term_taxonomy where taxonomy = '";
	switch ($type) {
	case "cat":
		$query .= "category";
		break;
	case "tag":
	default:
		$query .= "post_tag";
		break;
	}
	$query .= "' and count > 1) and t.term_taxonomy_id = r.term_taxonomy_id order by t.count, mix";
	$results = $wpdb->get_results($query);
	if (count($results)) {
		$now = gmdate("Y-m-d H:i:s",(time()+($time_difference*3600)));
		foreach ($results as $result) {
			$potential += (1 / $result->rarity);
			$query = "select object_id as ID, rand() as remix from $wpdb->term_relationships where term_taxonomy_id = $result->ttid and object_id != $post->ID and object_id in (select ID from $wpdb->posts where post_date <= '$now'";
			if ($wp_version > 2.5) {
				$query .= " and post_parent = 0";
			}
			$query .= ") order by remix";
			$subsets = $wpdb->get_results($query);
			if (count($subsets)) {
				foreach ($subsets as $connection) {
					if (!array_search($connection->ID,$id_list)) {
						if ($id_list[0] == $connection->ID) {
							$strength_list[0] += 1/$result->rarity;
						} else {
							array_push($id_list,$connection->ID);
							array_push($strength_list,1/$result->rarity);
						}
					} else {
						$i = array_search($connection->ID,$id_list);
						$strength_list[$i] += 1/$result->rarity;
					}
				}
			}
		}
	}
	if (sizeof($strength_list) > 1 ) {
		array_multisort($strength_list,SORT_DESC,$id_list);
	}
	while(sizeof($id_list) > 0) {
		$set = array("post_id"=>array_shift($id_list), "strength"=>number_format((array_shift($strength_list) / $potential),3));
		array_push($list,$set);
	}
	return $list;
}

function wp_mix_lists($taglist, $catlist) {
	$options = get_option(basename(__FILE__, ".php"));
	$list = array();
	$id_list = array();
	$strength_list = array();
	$tagweight = stripslashes($options['tag_weight']);
	$catweight = stripslashes($options['cat_weight']);
	if ($tagweight + $catweight == 0) {
		$tagweight = 1;
		$catweight = 1;
	}
	while(sizeof($taglist) > 0) {
		array_push($id_list,$taglist[0]['post_id']);
		array_push($strength_list,($tagweight * $taglist[0]['strength']));
		array_shift($taglist);
	}

	while(sizeof($catlist) > 0) {
		if (!array_search($catlist[0]['post_id'],$id_list)) {
			if ($id_list[0] == $catlist[0]['post_id']) {
				$strength_list[0] += ($catweight * $catlist[0]['strength']);
			} else {
				array_push($id_list,$catlist[0]['post_id']);
				array_push($strength_list,($catweight * $catlist[0]['strength']));
			}
		} else {
			$i = array_search($catlist[0]['post_id'],$id_list);
			$strength_list[$i] += ($catweight * $catlist[0]['strength']);
		}
		array_shift($catlist);
	}
	if (sizeof($strength_list) > 1 ) {
		array_multisort($strength_list,SORT_DESC,$id_list);
	}
	while(sizeof($id_list) > 0) {
		$set = array("post_id"=>array_shift($id_list), "strength"=>number_format((array_shift($strength_list) / ($tagweight + 

$catweight)),3));
		array_push($list,$set);
	}
	return $list;
}


// Prepare the default set of options
$default_options['limit'] = 5;
$default_options['none_text'] = '<ul><li>'.__('Unique Post', 'wp_keywordlink').'</li></ul>';
$default_options['prefix'] = '<h2>Related Posts</h2><ul>';
$default_options['suffix'] = '</ul>';
$default_options['format'] = 'value';
$default_options['output_template'] = '<li>{link} ({strength})</li>';
$default_options['auto_prefix'] = '<h2>Related Posts</h2><ul>';
$default_options['auto_suffix'] = '</ul>';
$default_options['auto_format'] = 'value';
$default_options['auto_output_template'] = '<li>{link} ({strength})</li>';
$default_options['tag_weight'] = 1;
$default_options['cat_weight'] = 1;
$default_options['text_strong'] = '<strong>'.__('Strong', 'wp_keywordlink').'</strong>';
$default_options['text_mild'] = __('Mild', 'wp_keywordlink');
$default_options['text_weak'] = __('Weak', 'wp_keywordlink');
$default_options['text_tenuous'] = '<em>'.__('Tenuous', 'wp_keywordlink').'</em>';
$default_options['one_extra'] = 'false';
$default_options['sim_rss'] = 'yes';
// the plugin options are stored in the options table under the name of the plugin file sans extension
add_option(basename(__FILE__, ".php"), $default_options, 'options for the Similarity plugin');

// This method displays, stores and updates all the options
function wp_options_page(){
	global $wpdb;
	// This bit stores any updated values when the Update button has been pressed
	if (isset($_POST['update_options'])) {
		// Fill up the options array as necessary
		$options['limit'] = $_POST['limit'];
		$options['none_text'] = $_POST['none_text'];
		$options['prefix'] = $_POST['prefix'];
		$options['suffix'] = $_POST['suffix'];
		$options['format'] = $_POST['format'];
		$options['output_template'] = $_POST['output_template'];
		$options['auto_prefix'] = $_POST['auto_prefix'];
		$options['auto_suffix'] = $_POST['auto_suffix'];
		$options['auto_format'] = $_POST['auto_format'];
		$options['auto_output_template'] = $_POST['auto_output_template'];
		$options['tag_weight'] = $_POST['tag_weight'];
		$options['cat_weight'] = $_POST['cat_weight'];
		$options['text_strong'] = $_POST['text_strong'];
		$options['text_mild'] = $_POST['text_mild'];
		$options['text_weak'] = $_POST['text_weak'];
		$options['text_tenuous'] = $_POST['text_tenuous'];
		$options['one_extra'] = $_POST['one_extra'];
		if ((floatval($_POST['minimum_strength']) < 0) || (floatval($_POST['minimum_strength']) > 1)) {
			$options['minimum_strength'] = 0;
		} else {
			$options['minimum_strength'] = floatval($_POST['minimum_strength']);
		}
		if ((floatval($_POST['random_min']) < 0) || (floatval($_POST['random_min']) > 1)) {
			$options['random_min'] = $options['minimum_strength'];
		} else {
			$options['random_min'] = floatval($_POST['random_min']);
		}
		$options['adf'] = $_POST['adf'];
		$options['sim_pages'] = $_POST['sim_pages'];
		$options['sim_rss'] = $_POST['sim_rss'];

		// store the option values under the plugin filename
		update_option(basename(__FILE__, ".php"), $options);
		
		// Show a message to say we've done something
		echo '<div class="updated"><p>' . __('Options saved', 'wp_keywordlink') . '</p></div>';
	} elseif (isset($_POST['restore_defaults'])) {
		$options['limit'] = 5;
		$options['none_text'] = '<ul><li>'.__('Unique Post', 'wp_keywordlink').'</li></ul>';
		$options['prefix'] = '<ul>';
		$options['suffix'] = '</ul>';
		$options['format'] = 'value';
		$options['output_template'] = '<li>{link} ({strength})</li>';
		$options['auto_prefix'] = '<ul>';
		$options['auto_suffix'] = '</ul>';
		$options['auto_format'] = 'value';
		$options['auto_output_template'] = '<li>{link} ({strength})</li>';
		$options['tag_weight'] = 1;
		$options['cat_weight'] = 1;
		$options['text_strong'] = '<strong>'.__('Strong', 'wp_keywordlink').'</strong>';
		$options['text_mild'] = __('Mild', 'wp_keywordlink');
		$options['text_weak'] = __('Weak', 'wp_keywordlink');
		$options['text_tenuous'] = '<em>'.__('Tenuous', 'wp_keywordlink').'</em>';
		$options['one_extra'] = 'false';
		$options['minimum_strength'] = 0;
		$options['random_min'] = 0;

		// store the option values under the plugin filename
		update_option(basename(__FILE__, ".php"), $options);
		
		// Show a message to say we've done something
		echo '<div class="updated"><p>' . __('Factory Settings applied', 'wp_keywordlink') . '</p></div>';
	} else {
		// If we are just displaying the page we first load up the options array
		$options = get_option(basename(__FILE__, ".php"));
	}
	//now we drop into html to display the option page form
	?>
		<div class="wrap">
		<h2><?php _e('Similarity Options', 'wp_keywordlink'); ?></h2>
		<form method="post" action="">
		<fieldset class="options">
		<table class="optiontable">
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Number of posts to show', 'wp_keywordlink') ?>:</th>
				<td colspan="2"><input name="limit" type="text" id="limit" value="<?php echo $options['limit']; ?>" size="2" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Minimum match strength', 'wp_keywordlink') ?>:</th>
				<td colspan="2"><input name="minimum_strength" type="text" id="minimum_strength" value="<?php echo $options['minimum_strength']; ?>" size="5" /> <?php _e('(Any number between .00 and 1 with 1 being a perfect match.)', 'wp_keywordlink') ?></td>
			</tr>
			<tr valign="top">

				<th scope="row" align="right"><?php _e('Default display if no matches', 'wp_keywordlink') ?>:</th>
				<td colspan="2"><input name="none_text" type="text" id="none_text" value="<?php echo htmlspecialchars(stripslashes($options['none_text'])); ?>" size="40" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"></th>
				<td align="center"><strong><?php _e('Template functions', 'wp_keywordlink') ?></strong></td>
				<td align="center"><strong><?php _e('Auto display functions', 'wp_keywordlink') ?></strong></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Text and codes before the list', 'wp_keywordlink') ?>:</th>
				<td><input name="prefix" type="text" id="prefix" value="<?php echo htmlspecialchars(stripslashes($options['prefix'])); ?>" size="40" /></td>
				<td><input name="auto_prefix" type="text" id="auto_prefix" value="<?php echo htmlspecialchars(stripslashes($options['auto_prefix'])); ?>" size="40" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Text and codes after the list', 'wp_keywordlink') ?>:</th>
				<td><input name="suffix" type="text" id="suffix" value="<?php echo htmlspecialchars(stripslashes($options['suffix'])); ?>" size="40" /></td>
				<td><input name="auto_suffix" type="text" id="auto_suffix" value="<?php echo htmlspecialchars(stripslashes($options['auto_suffix'])); ?>" size="40" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Output template', 'wp_keywordlink') ?>:</th>
				<td><textarea name="output_template" id="output_template" rows="4" cols="32"><?php echo htmlspecialchars(stripslashes($options['output_template'])); ?></textarea></td>
				<td><textarea name="auto_output_template" id="auto_output_template" rows="4" cols="32"><?php echo htmlspecialchars(stripslashes($options['auto_output_template'])); ?></textarea></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"></th>
				<td colspan="2" align="center"><?php _e('Valid template tags', 'wp_keywordlink') ?>:{link}, {strength}, {url}, {title}</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Display format for similarity strength', 'wp_keywordlink') ?>:</th>
				<td colspan="2">
					<input type="radio" name="format" id="format" value="color"<?php if ($options['format'] == 'color') echo ' checked'; ?>><?php _e('Visual', 'wp_keywordlink') ?></input>&nbsp;
					<input type="radio" name="format" id="format" value="percent"<?php if ($options['format'] == 'percent') echo ' checked'; ?>><?php _e('Percent', 'wp_keywordlink') ?></input>&nbsp;
					<input type="radio" name="format" id="format" value="text"<?php if ($options['format'] == 'text') echo ' checked'; ?>><?php _e('Text', 'wp_keywordlink') ?></input>&nbsp;
					<input type="radio" name="format" id="format" value="value"<?php if ($options['format'] == 'value') echo ' checked'; ?>><?php _e('Value', 'wp_keywordlink') ?></input>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Custom text for strength', 'wp_keywordlink') ?>:</th>
				<td colspan="2"><input name="text_strong" type="text" id="text_strong" value="<?php echo htmlspecialchars(stripslashes($options['text_strong'])); ?>" size="40" /> &gt;75%</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right">&nbsp;</th>
				<td colspan="2"><input name="text_mild" type="text" id="text_mild" value="<?php echo htmlspecialchars(stripslashes($options['text_mild'])); ?>" size="40" /> 75% &gt; 50%</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right">&nbsp;</th>
				<td colspan="2"><input name="text_weak" type="text" id="text_weak" value="<?php echo htmlspecialchars(stripslashes($options['text_weak'])); ?>" size="40" /> 50% &gt; 25%</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right">&nbsp;</th>
				<td colspan="2"><input name="text_tenuous" type="text" id="text_tenuous" value="<?php echo htmlspecialchars(stripslashes($options['text_tenuous'])); ?>" size="40" /> &lt; 25%</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Relative mixing weights', 'wp_keywordlink') ?>:</th>
				<td colspan="2"><input name="tag_weight" type="text" id="tag_weight" value="<?php echo htmlspecialchars(stripslashes($options['tag_weight'])); ?>" size="40" /> <?php _e('Tags', 'wp_keywordlink') ?></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right">&nbsp;</th>
				<td colspan="2"><input name="cat_weight" type="text" id="cat_weight" value="<?php echo htmlspecialchars(stripslashes($options['cat_weight'])); ?>" size="40" /> <?php _e('Categories', 'wp_keywordlink') ?></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Show one more random related post', 'wp_keywordlink') ?>:</th>
				<td colspan="2">
					<input type="radio" name="one_extra" id="one_extra" value="true"<?php if ($options['one_extra'] == 'true') echo ' checked'; ?>><?php _e('Yes', 'wp_keywordlink') ?></input>&nbsp;
					<input type="radio" name="one_extra" id="one_extra" value="false"<?php if ($options['one_extra'] == 'false') echo ' checked'; ?>><?php _e('No', 'wp_keywordlink') ?></input>&nbsp;
					<strong><?php _e('Minimum match strength', 'wp_keywordlink') ?></strong><?php _e(' (optional)', 'wp_keywordlink') ?>:&nbsp; 
					<input name="random_min" type="text" id="random_min" value="<?php echo $options['random_min']; ?>" size="5" />				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Auto-display list from function', 'wp_keywordlink') ?>:</th>
				<td colspan="2">
					<input type="radio" name="adf" id="adf" value="none"<?php if ($options['adf'] == 'none') echo ' checked'; ?>><?php _e('None', 'wp_keywordlink') ?></input>&nbsp;
					<input type="radio" name="adf" id="adf" value="tag"<?php if ($options['adf'] == 'tag') echo ' checked'; ?>>sim_by_tag</input>&nbsp;
					<input type="radio" name="adf" id="adf" value="cat"<?php if ($options['adf'] == 'cat') echo ' checked'; ?>>sim_by_cat</input>&nbsp;
					<input type="radio" name="adf" id="adf" value="mix"<?php if ($options['adf'] == 'mix') echo ' checked'; ?>>sim_by_mix</input>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Display Similarity on pages', 'wp_keywordlink') ?>:</th>
				<td colspan="2">
					<input type="radio" name="sim_pages" id="sim_pages" value="yes"<?php if ($options['sim_pages'] == 'yes') echo ' checked'; ?>><?php _e('Yes', 'wp_keywordlink') ?></input>&nbsp;
					<input type="radio" name="sim_pages" id="sim_pages" value="no"<?php if ($options['sim_pages'] == 'no') echo ' checked'; ?>><?php _e('No', 'wp_keywordlink') ?></input>
					(<?php _e('Only useful with if your pages have tags and/or categories assigned.', 'wp_keywordlink') ?>)
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Display Similarity on Feed', 'wp_keywordlink') ?>:</th>
				<td colspan="2">
					<input type="radio" name="sim_rss" id="sim_rss" value="yes"<?php if ($options['sim_rss'] == 'yes') echo ' checked'; ?>><?php _e('Yes', 'wp_keywordlink') ?></input>&nbsp;
					<input type="radio" name="sim_rss" id="sim_rss" value="no"<?php if ($options['sim_rss'] == 'no') echo ' checked'; ?>><?php _e('No', 'wp_keywordlink') ?></input>
				</td>
			</tr>
		</table>
		</fieldset>
		<div class="submit">
			<input type="submit" name="update_options" value="<?php _e('Update', 'wp_keywordlink') ?>"  style="font-weight:bold;" />
			<input type="submit" name="restore_defaults" value="<?php _e('Restore Defaults', 'wp_keywordlink') ?>"  style="font-weight:bold;" />
		</div>
		</form>    		
	</div>
	<?php	
}

function wp_similarly_help(){
	print "<h3><a href=http://www.davidjmiller.org/2008/similarity/>";
	_e('Help and Instructions From Similarity Plugin', 'wp_keywordlink');
	print "</a></h3>";
	?>
<p><b><?php _e('The plugin allows for three function calls anywhere in your page templates (all use the same options):', 'wp_keywordlink'); ?></b></p>

<p>* <code>&lt;?php wp_sim_by_tag(); ?&gt;</code> - determines similarity based on the tags applied to the posts</p>
<p>* <code>&lt;?php wp_sim_by_cat(); ?&gt;</code> - determines similarity based on the categories assigned to the posts</p>
<p>* <code>&lt;?php wp_sim_by_mix(); ?&gt;</code> - determines similarity based on the tags and categories assigned to the posts</p>

<p>* <code>&lt;?php wp_sim_by_tag_multi(); ?&gt;</code> - determines similarity based on the tags applied to the first post on milti-post pages</p>
<p>* <code>&lt;?php wp_sim_by_cat_multi(); ?&gt;</code> - determines similarity based on the categories assigned to the first post on milti-post pages</p>
<p>* <code>&lt;?php wp_sim_by_mix_multi(); ?&gt;</code> - determines similarity based on the categories and tags assigned to the first post on milti-post pages weighting each according to the ratio you assign</p>

<p><b><?php _e('There are also three function calls that are better suited for use in sidebars (specifically when the sidebar is used on the main page)', 'wp_keywordlink'); ?></b></p>

<p>* To display a Similarity list as a widget use a text widget with one of the following shortcodes - <code>[WP-SIM-BY-TAG]</code> <code>[WP-SIM-BY-CAT]</code> <code>[WP-SIM-BY-MIX]</code> for single post pages only or for all pages use <code>[WP-SIM-BY-TAG-MULTI]</code> <code>[WP-SIM-BY-CAT-MULTI]</code> or <code>[WP-SIM-BY-MIX-MULTI]</code></p>
<p>* To display a Similarity list without altering your templates simply select the function that you would like to auto-display at the bottom of the options page.</p>
   <?php
}


$options = get_option(basename(__FILE__, ".php"));
add_shortcode('WP-SIM-BY-TAG', 'wp_short_tag');
add_shortcode('WP-SIM-BY-CAT', 'wp_short_cat');
add_shortcode('WP-SIM-BY-MIX', 'wp_short_mix');
add_shortcode('WP-SIM-BY-TAG-MULTI', 'wp_sim_by_tag_multi');
add_shortcode('WP-SIM-BY-CAT-MULTI', 'wp_sim_by_cat_multi');
add_shortcode('WP-SIM-BY-MIX-MULTI', 'wp_sim_by_mix_multi');
add_filter('the_content','wp_auto_display',1000);
?>
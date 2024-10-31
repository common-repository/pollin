<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$colors = array('bf1a1d', 'bfa024', '830088', '3e4cb9', '52803b', '805b6f', '827f71', '4bc0e0', 'c12a8f', '000000');
$color_index = 0;
?>

<div class="wrap">
<h2><?php e("Poll"); ?></h2><?php

$question_id = $_REQUEST['question'];

print "<p>" . stripslashes($wpdb->get_var("SELECT question FROM {$wpdb->prefix}pollin_question WHERE ID=$question_id")) . "</p>";

//Show result.
$answers = $wpdb->get_results($wpdb->prepare("SELECT ID, answer, votes FROM {$wpdb->prefix}pollin_answer WHERE question_ID=%d ORDER BY sort_order", $question_id));
?>
<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><?php e('Answer') ?></th>
		<th scope="col"><?php e('Votes') ?></th>
		<th scope="col" colspan="3"><?php e('Vote Count/Percentage') ?></th>
	</tr>
	</thead>
	<tbody id="the-list">
<?php
//First find the total number of votes
$total = 0;
foreach($answers as $ans) $total += $ans->votes;

// Show each answer with the number of votes it recived.
foreach($answers as $ans) {
	print "<tr><td>";
	if(isset($user_answer) and $ans->ID == $user_answer) print "<strong>" . stripslashes($ans->answer) . "</strong>"; //Users answer.
	else print stripslashes($ans->answer);
	print "</td>";
	
	if($total == 0) $percent = 0;
	else $percent = intval(($ans->votes / $total) * 100);
	$color = pollin_nextColor();
	print "<td class='pollin-result-bar-holder' style='width:200px;'><div class='pollin-result-bar' style='background-color:$color; width:$percent%;'>&nbsp;</div></td>";
	print "<td>{$ans->votes} " . t("Votes") . "($percent%)</td>";
	print "</tr>";
}
?></tbody>
</table><br /><br />
<strong><?php e("Total Votes"); ?>: <?=$total?></strong><br />
<a href="edit.php?page=pollin/question.php"><?php e("Manage Polls") ?></a>
</div>
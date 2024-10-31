<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {
	if($action == 'edit'){ //Update goes here
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}pollin_question SET question='%s', status='%d' WHERE ID='%d'", $_REQUEST['content'], $_REQUEST['status'], $_REQUEST['question']));
		$wpdb->query("DELETE FROM {$wpdb->prefix}pollin_answer WHERE question_ID='$_REQUEST[question]'");
		wpframe_message('Question updated.');
		
	} else { // New Question Insert goes here.
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}pollin_question(question, added_on, status) VALUES('%s', NOW(), '%d')", $_REQUEST['content'], $_REQUEST['status']));//Inserting the questions;
		wpframe_message('Question added.');
		$_REQUEST['question'] = $wpdb->insert_id;
		$action='edit';
	}
	$question_id = $_REQUEST['question'];
	
	$counter = 1;
	
	foreach ($_REQUEST['answer'] as $answer_text) {
		 //Inserting answers for updating after deleting the rows;
		if($answer_text) {
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}pollin_answer(question_ID, answer, votes, sort_order) "
							. " VALUES(%d,'%s', 0, $counter)", $question_id, $answer_text));
			$counter++;
		}
	}
}


if($_REQUEST['action']=='delete') {
	$wpdb->query("DELETE FROM {$wpdb->prefix}pollin_answer WHERE question_ID='$_REQUEST[question]'");
	$wpdb->query("DELETE FROM {$wpdb->prefix}pollin_question WHERE ID='$_REQUEST[question]'");
	print '<div id="message" class="updated fade"><p>' . t("Question Deleted."). '</p></div>';
}
?>

<div class="wrap">
<h2><?php e("Manage Polls") ?></h2>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;">#</div></th>
		<th scope="col"><?php e('Question') ?></th>
		<th scope="col"><?php e('Number Of Answers') ?></th>
		<th scope="col"><?php e('Status') ?></th>
		<th scope="col" colspan="3"><?php e('Action') ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
// Retrieve the quetions
$all_question = $wpdb->get_results("SELECT Q.ID,Q.question,Q.status,(SELECT COUNT(*) FROM {$wpdb->prefix}pollin_answer WHERE question_ID=Q.ID) AS answer_count
										FROM `{$wpdb->prefix}pollin_question` AS Q");

if (count($all_question)) {
	$bgcolor = '';
	$class = ('alternate' == $class) ? '' : 'alternate';
	$question_count = 0;
	$status = array(t('Inactive'), t('Active'));
	
	foreach($all_question as $question) {
		$question_count++;
		print "<tr id='question-{$question->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $question_count ?></th>
		<td><?php echo stripslashes($question->question) ?></td>
		<td><?php echo $question->answer_count ?></td>
		<td><?php echo $status[$question->status]?></a></td>
		<td><a href='edit.php?page=pollin/poll_result.php&amp;question=<?php echo $question->ID?>&amp;action=edit' class='edit'><?php e('Poll Result'); ?></a></td>
		<td><a href='edit.php?page=pollin/question_form.php&amp;question=<?php echo $question->ID?>&amp;action=edit' class='edit'><?php e('Edit'); ?></a></td>
		<td><a href='edit.php?page=pollin/question.php&amp;action=delete&amp;question=<?php echo $question->ID?>' class='delete' onclick="return confirm('<?php e(addslashes("You are about to delete this question. This will delete the answers to this question. Press 'OK' to delete and 'Cancel' to stop."))?>');"><?=__('Delete')?></a></td>
		</tr>
<?php
		}
	} else {
?>
	<tr style='background-color: <?php echo $bgcolor; ?>;'>
		<td colspan="4"><?php e('No questions found.') ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>

<a href="edit.php?page=pollin/question_form.php&amp;action=new"><?php e("Create a New Poll")?></a>
</div>

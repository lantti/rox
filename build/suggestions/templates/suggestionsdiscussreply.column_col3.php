<?php
$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
include 'suggestionserrors.php';
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['suggestion-post-title'] = '';
    $vars['suggestion-post-text'] = '';
}

// Show suggestion head (as on every page)
include 'suggestion.php';

if (!$this->viewOnly) {
    // Now load the board and show it
    $Forums = new ForumsController;
    $Forums->showExternalSuggestionsThreadReply( $this->suggestion->id, $this->model->getGroupId(), $this->suggestion->threadId, 'discuss');
}
?>
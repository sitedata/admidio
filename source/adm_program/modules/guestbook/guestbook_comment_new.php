<?php
/******************************************************************************
 * Create and edit guestbook comments
 *
 * Copyright    : (c) 2004 - 2013 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Parameters:
 *
 * id            - ID des Eintrages, dem ein Kommentar hinzugefuegt werden soll
 * cid           - ID des Kommentars der editiert werden soll
 * headline      - Ueberschrift, die ueber den Einraegen steht
 *                 (Default) Gaestebuch
 *
 *****************************************************************************/

require_once('../../system/common.php');

// Initialize and check the parameters
$getGboId    = admFuncVariableIsValid($_GET, 'id', 'numeric', 0);
$getGbcId    = admFuncVariableIsValid($_GET, 'cid', 'numeric', 0);
$getHeadline = admFuncVariableIsValid($_GET, 'headline', 'string', $gL10n->get('GBO_GUESTBOOK'));

// pruefen ob das Modul ueberhaupt aktiviert ist
if ($gPreferences['enable_guestbook_module'] == 0)
{
    // das Modul ist deaktiviert
    $gMessage->show($gL10n->get('SYS_MODULE_DISABLED'));
}

// Es muss ein (nicht zwei) Parameter uebergeben werden: Entweder id oder cid...
if($getGboId > 0 && $getGbcId > 0)
{
    $gMessage->show($gL10n->get('SYS_INVALID_PAGE_VIEW'));
}

//Erst einmal die Rechte abklopfen...
if(($gPreferences['enable_guestbook_module'] == 2 || $gPreferences['enable_gbook_comments4all'] == 0)
&& $getGboId > 0)
{
    // Falls anonymes kommentieren nicht erlaubt ist, muss der User eingeloggt sein zum kommentieren
    require_once('../../system/login_valid.php');

    if (!$gCurrentUser->commentGuestbookRight())
    {
        // der User hat kein Recht zu kommentieren
        $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
    }
}

if($getGbcId > 0)
{
    // Zum editieren von Kommentaren muss der User auch eingeloggt sein
    require_once('../../system/login_valid.php');

    if (!$gCurrentUser->editGuestbookRight())
    {
        // der User hat kein Recht Kommentare zu editieren
        $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
    }

}

$gNavigation->addUrl(CURRENT_URL);

// Gaestebuchkommentarobjekt anlegen
$guestbook_comment = new TableGuestbookComment($gDb);

if($getGbcId > 0)
{
    $guestbook_comment->readDataById($getGbcId);

    // Pruefung, ob der Eintrag zur aktuellen Organisation gehoert
    if($guestbook_comment->getValue('gbo_org_id') != $gCurrentOrganization->getValue('org_id'))
    {
        $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
    }
}

if(isset($_SESSION['guestbook_comment_request']))
{
    // durch fehlerhafte Eingabe ist der User zu diesem Formular zurueckgekehrt
    // nun die vorher eingegebenen Inhalte ins Objekt schreiben
	$guestbook_comment->setArray($_SESSION['guestbook_comment_request']);
    unset($_SESSION['guestbook_comment_request']);
}

// Wenn der User eingeloggt ist und keine cid uebergeben wurde
// koennen zumindest Name und Emailadresse vorbelegt werden...
if($getGbcId == 0 && $gValidLogin)
{
    $guestbook_comment->setValue('gbc_name', $gCurrentUser->getValue('FIRST_NAME'). ' '. $gCurrentUser->getValue('LAST_NAME'));
    $guestbook_comment->setValue('gbc_email', $gCurrentUser->getValue('EMAIL'));
}


if (!$gValidLogin && $gPreferences['flooding_protection_time'] != 0)
{
    // Falls er nicht eingeloggt ist, wird vor dem Ausfuellen des Formulars noch geprueft ob der
    // User innerhalb einer festgelegten Zeitspanne unter seiner IP-Adresse schon einmal
    // einen GB-Eintrag erzeugt hat...
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    $sql = 'SELECT count(*) FROM '. TBL_GUESTBOOK_COMMENTS. '
            where unix_timestamp(gbc_timestamp_create) > unix_timestamp()-'. $gPreferences['flooding_protection_time']. '
              and gbc_ip_address = \''. $guestbook_comment->getValue('gbc_ip_address'). '\'';
    $result = $gDb->query($sql);
    $row = $gDb->fetch_array($result);
    if($row[0] > 0)
    {
          //Wenn dies der Fall ist, gibt es natuerlich keinen Gaestebucheintrag...
          $gMessage->show($gL10n->get('GBO_FLOODING_PROTECTION', $gPreferences['flooding_protection_time']));
    }
}

// create html page object
$page = new HtmlPage();

// show back link
$page->addHtml($gNavigation->getHtmlBackButton());

// Html-Kopf ausgeben
if($getGboId > 0)
{
    $id   = $getGboId;
    $mode = '4';
    $page->addHeadline($gL10n->get('GBO_CREATE_COMMENT'));
}
else
{
    $id   = $getGbcId;
    $mode = '8';
    $page->addHeadline($gL10n->get('GBO_EDIT_COMMENT'));
}

// show form
$form = new HtmlForm('guestbook_comment_edit_form', $g_root_path.'/adm_program/modules/guestbook/guestbook_function.php?id='.$id.'&amp;headline='.$getHeadline.'&amp;mode='.$mode, $page);
$form->openGroupBox('gb_contact_details', $gL10n->get('SYS_CONTACT_DETAILS'));
if ($gCurrentUser->getValue('usr_id') > 0)
{
    // registered users should not change their name
    $form->addTextInput('gbc_name', $gL10n->get('SYS_NAME'), $guestbook_comment->getValue('gbc_name'), 60, FIELD_DISABLED);
}
else
{
    $form->addTextInput('gbc_name', $gL10n->get('SYS_NAME'), $guestbook_comment->getValue('gbc_name'), 60, FIELD_MANDATORY);
}
$form->addTextInput('gbc_email', $gL10n->get('SYS_EMAIL'), $guestbook_comment->getValue('gbc_email'), 50);
$form->closeGroupBox();
$form->openGroupBox('gb_message', $gL10n->get('SYS_COMMENT'));
$form->addEditor('gbc_text', null, $guestbook_comment->getValue('gbc_text'), FIELD_MANDATORY, 'AdmidioGuestbook');
$form->closeGroupBox();

// if captchas are enabled then visitors of the website must resolve this
if (!$gValidLogin && $gPreferences['enable_mail_captcha'] == 1)
{
    $form->openGroupBox('gb_confirmation_of_entry', $gL10n->get('SYS_CONFIRMATION_OF_INPUT'));
    $form->addCaptcha('captcha', $gPreferences['captcha_type']);
    $form->closeGroupBox();
}

// show informations about user who creates the recordset and changed it
$form->addHtml(admFuncShowCreateChangeInfoById($guestbook_comment->getValue('gbc_usr_id_create'), $guestbook_comment->getValue('gbc_timestamp_create'), $guestbook_comment->getValue('gbc_usr_id_change'), $guestbook_comment->getValue('gbc_timestamp_change')));
$form->addSubmitButton('btn_save', $gL10n->get('SYS_SAVE'), THEME_PATH.'/icons/disk.png');

// add form to html page and show page
$page->addHtml($form->show(false));
$page->show();

?>
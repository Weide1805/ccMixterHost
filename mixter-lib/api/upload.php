<?

require_once( 'mixter-lib/lib/status.inc' );
require_once( 'mixter-lib/lib/upload.php' );

define('INVALID_UPLOAD_ID',  'invalid upload id');


CCEvents::AddHandler(CC_EVENT_MAP_URLS,      array( 'CCEventsUpload',  'OnMapUrls'));


class CCEventsUpload
{
  /**
  * Event handler for mapping urls to methods
  *
  * @see CCEvents::MapUrl
  */
  function OnMapUrls()
  {
    CCEvents::MapUrl( ccp('api','upload','permissions'), array('CCAPIUpload','Permissions'),
      CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '', _('Return various permissions on upload per user'), CC_AG_UPLOAD);
    CCEvents::MapUrl( ccp('api','upload','rate'), array('CCAPIUpload','Rate'),
      CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '', _('Rate/recommend an upload'), CC_AG_UPLOAD);
    CCEvents::MapUrl( ccp('api','upload','review'), array('CCAPIUpload','Review'),
      CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '', _('Rate/recommend an upload'), CC_AG_UPLOAD);
  }

}

class CCAPIUpload
{
  function Permissions($upload_id,$user_name)
  {

    if( !$this->_validate_upload_id($upload_id) ) {
      $status = _make_err_status(INVALID_UPLOAD_ID);
    } else if( !$this->_get_user_id($user_name) ) {
      $status = _make_err_status(UNKNOWN_USER);      
    } else {
      $lib = new CCLibUpload();
      $status = $lib->Permissions($upload_id,$user_name);
    }
    CCUtil::ReturnAjaxObj($status);
  }

  function Rate($upload_id,$user_name) {
    if( !$this->_validate_upload_id($upload_id) ) {
      $status = _make_err_status(INVALID_UPLOAD_ID);
    } else if( !$this->_get_user_id($user_name) ) {
      $status = _make_err_status(UNKNOWN_USER);      
    } else {
      $lib = new CCLibUpload();
      $status = $lib->Rate($upload_id,$user_name);
    }
    CCUtil::ReturnAjaxObj($status);    
  }

  function Review($upload_id,$user_name) {
    if( !$this->_validate_upload_id($upload_id) ) {
      $status = _make_err_status(INVALID_UPLOAD_ID);
    } else if( !$this->_get_user_id($user_name) ) {
      $status = _make_err_status(UNKNOWN_USER);      
    } else {
      $text = CCUtil::StripText($_REQUEST['textbody']);
      $lib = new CCLibUpload();
      $status = $lib->Review($upload_id,$user_name,$text);
    }
    CCUtil::ReturnAjaxObj($status);        
  }

  function _validate_upload_id($upload_id)
  {
    $upload_id = CCUtil::CleanNumber($upload_id);
    if( empty($upload_id) ) {
      return false;
    }
    $sql =<<<EOF
      SELECT upload_id
      FROM cc_tbl_uploads
      WHERE upload_id={$upload_id} AND
          upload_published > 0 AND
          upload_banned < 1
EOF;
    $upload_id = (int)CCDatabase::QueryItem($sql);
    return $upload_id > 0;
  }

  function _get_user_id($user_name) {
    $user_name = CCUtil::StripText($user_name);
    if( empty($user_name) ) {
      return false;
    }
    return CCUser::IDFromName($user_name);
  }
}
?>

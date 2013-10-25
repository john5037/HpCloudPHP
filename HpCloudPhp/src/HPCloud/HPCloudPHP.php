<?php 

/**
 * HPCLOUD REST API Class
 *
 * @package   	HPCLOUD
 * @category  	Libraries
 * @author	Nayak Kamal
 * @license	MIT License
 * @link	
 */

namespace HPCloud;

require_once 'Bootstrap.php';


  
use HPCloud\Bootstrap;
use HPCloud\Services\IdentityServices;
use HPCloud\Storage\ObjectStorage;
use HPCloud\Storage\ObjectStorage\Object;


class HPCloudPHP {
	
	////////////////////////////////////////
	/// Settings Variables
	/// (Edit to configure)
	////////////////////////////////////////
	
 /**
	* Variable:	$identityUrl
	* Description:	The Identity Service
	* Example:	https://region-a.geo-1.identity.hpcloudsvc.com:35357/v2.0/
	*/
	private $identity_url = "https://region-a.geo-1.identity.hpcloudsvc.com:35357/v2.0/";
 
 /**
	* Variable:	$messagingUrl
	* Description:	The Messaging Service Service
	* Example:	https://region-a.geo-1.messaging.hpcloudsvc.com/v1.1/
	*/
	private $messaging_url = "https://region-a.geo-1.messaging.hpcloudsvc.com/v1.1/";
		
	/**
	 * Variable: $streamTransport
	 *
	 */      	
	 
	private $streamTransport = "\HPCloud\Transport\PHPStreamTransport"; 
	/**
	* Variable:	$token
	* Description:	The token ID for REST calls
	*/
	private $token;
	
	/**
	* Variable:	$logged_in
	* Description:	Boolean flag for login status
	*/
	private $logged_in;
	
	/**
	* Variable:	$error
	* Description:	The latest error
	*/
	private $error = FALSE;
	
	/**
	 * Varaible: $identity
	 * Description: Identity 	 
	 * 
	**/
  private $identity = '';      	
  /**
	 * Varaible: $mime
	 * Description: MimeType 	 
	 * 
	**/
  private $mime = 'image/jpeg';      	
  
 /**
	* Function:	HPCloud()
	* Parameters: 	none	
	* Description:	Class constructor
	* Returns:	TRUE on login success, otherwise FALSE
	*/
	function __construct($account=null,$secreat=null,$tenantId=null,$identity_url=null,$stream_transport=null) 
	{
	  
    if($identity_url == null)
      $identity_url = $this->identity_url;
      
    if($stream_transport == null)
      $streamtransport= $this->streamTransport;
      
    Bootstrap::useAutoloader();
    Bootstrap::useStreamWrappers();
     
    $this->setConfigurations($account,$secreat,$tenantId,$identity_url,$streamtransport);  
    
    $this->identity = Bootstrap::identity();
    $this->tenantId = $tenantId;
    return $this->token = $this->identity->token();
	
  }
	
	private function setConfigurations($account,$secreat,$tenantId,$identity_url,$streamtransport)
	{
    $settings = array(
      'account' => $account,
      'secret' => $secreat,
      'tenantid' => $tenantId,
      'endpoint' => $identity_url,
      'transport' => $streamtransport,
      'transport.debug' => TRUE,
    );
    Bootstrap::setConfiguration($settings);
    return 1;
    
  }
  
	public function getToken()
	{
    return $this->token; 
  }
	
	/**
	* Function:	get_error()
	* Parameters: 	none	
	* Description:	Gets the current error. The current error is sent whenever
	*		an API call returns an error. When the function is called,
	*		it returns and clears the current error.
	* Returns:	Returns the error array in the form:
	*			array(
	*				'name' => [value],
	*				'number' => [value],
	*				'description'
	*			)
	*		If there is no error, returns FALSE.
	*		If the error array is corrupted, but there is still an
	*		error, returns TRUE.
	*/
	public function get_error() {
		if(isset($this->error['name'])) {
			$error = $this->error;
			$this->error = FALSE;
			return $error;
		} else if(is_bool($this->error)) {
			$error = $this->error;
			$this->error = FALSE;
			return $error;
		} else {
			return TRUE;
		}
	}
	
	
	/**
	* Function:	rest_request()
	* Parameters: 	$call_name	= (string) the API call name
	*	$call_arguments	= (array) the arguments for the API call
	* Description:	Makes an API call given a call name and arguments
	*		on the specific API calls
	* Returns:	An array wi
	*   th the API call response data
	*/
	
	private function rest_request($call_name, $call_arguments) {

		$ch = curl_init(); 
		
		$post_data = 'method='.$call_name.'&input_type=JSON&response_type=JSON';
		$jsonEncodedData = json_encode($call_arguments);
		$post_data = $post_data . "&rest_data=" . $jsonEncodedData;
		
    curl_setopt($ch, CURLOPT_URL, $this->rest_url); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $output = curl_exec($ch); 
		
		$response_data = json_decode($output,true);
		
		return $response_data;
	}

      
	
	/**
	* Function:	is_logged_in()
	* Parameters: 	none
	* Description:	Simple getter for logged_in private variable
	* Returns:	boolean
	*/
	function is_logged_in()
	{
	    return $this->logged_in;
	}

	/**
	* Function:	__destruct()
	* Parameters: 	none
	* Description:	Closes the API connection when the PHP class
	*		object is destroyed
	* Returns:	nothing
	*/
	function __destruct() {
	 unset($this->token);
	}
	
	function SaveToObjectStorage($container=null,$fileName=null,$fileContent=null,$subDir=null,$mime=null)
	{
    $catalog = $this->identity->serviceCatalog('object-store');
    $store = ObjectStorage::newFromServiceCatalog($catalog, $this->token);
    
    $container = $store->container($container);
    
    if($subDir != null)
       $fileName = $subDir."/".$fileName;
         
    $localObject = new Object($fileName,file_get_contents($fileContent,$this->mime));
    $container->save($localObject);
  }

 public function faceDetection($filename='',$url_object_store='')
 {
       
    $url_pic = $filename;
    
    $query_str = "url_pic=".$url_pic."&url_object_store=".$url_object_store."&filename=j1.jpg";
    
    //$url = "http://map-api.hpl.hp.com/facedetect".$query_str;
    $url = "http://map-api.hpl.hp.com/facedetect?".$query_str;
    
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  'X-Auth-Token: ' . $this->token,
  
  'mime: image/jpeg'  
));

    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    $response = curl_exec($ch);

    if($response === false)
    {
      echo 'Curl error: ' . curl_error($ch);die();
    }
      return $response;  
 }
 
 private function addQueue($queue_name='',$messaging_url='')
 {
    if($messaging_url == '')
            $messaging_url = $this->messaging_url.$this->tenantId;
         
    $url = $messaging_url.'/queues/'.$queue_name;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-Auth-Token: ' . $this->token,
    'Content-Type: ' . 'application/json' 
    ));
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $response = curl_exec($ch);
    if($response === false)
    {
      echo 'Curl error: ' . curl_error($ch);die();
    }
    return $response;
 }
 private function checkQueue($queue_name)
 {
     $flag = 0;
     $listQueues = json_decode($this->listQueue());
    
     foreach($listQueues->queues as $key=>$queue)
     {
       if($queue_name == $queue->name) {
         $flag= 1;   
         break;
       }
     }
     // if name is Exist then send Message to Queue
     if($flag == 1)
     {
      return true;
     }
     else {
       // First we have to create the queue
       $this->addQueue($queue_name);
       return true;
     }
     
 }
 public function sendMessageToQueue($queue_name='',$msg = '',$messaging_url='')
 {
 
    // First we have to check that queue is Exist or not if its not exist then we have to create the queue
    $this->checkQueue($queue_name);
    if($messaging_url == '')
            $messaging_url = $this->messaging_url.$this->tenantId;
    
    $url = $messaging_url.'/queues/'.$queue_name.'/messages';
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-Auth-Token: ' . $this->token,
    'Content-Type: ' . 'application/json' 
    ));
    $datapost = array('body'=> $msg);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $datapost);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
   
    $response = curl_exec($ch);
    if($response === false)
    {
      echo 'Curl error: ' . curl_error($ch);die();
    }
    return $response;
 }
 public function getMessageFromQueue($queue_name='',$messaging_url='')
 {
   if($messaging_url == '')
            $messaging_url = $this->messaging_url.$this->tenantId;   
    $url = $messaging_url.'/queues/'.$queue_name.'/messages';
    $ch = curl_init($url);   
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-Auth-Token: ' . $this->token,
    'Content-Type: ' . 'application/json' 
    ));
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
   
    $response = curl_exec($ch);
    if($response === false)
    {
      echo 'Curl error: ' . curl_error($ch);die();
    }
    return $response;
 }
 
 public function deleteQueue($queue_name='',$messaging_url='')
 {
    if($messaging_url == '')
      $messaging_url = $this->messaging_url.$this->tenantId;
    $url = $messaging_url.'/queues/'.$queue_name;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-Auth-Token: ' . $this->token,
    'Content-Type: ' . 'application/json' 
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $response = curl_exec($ch);
    if($response === false)
    {
      echo 'Curl error: ' . curl_error($ch);die();
    }
    return $response;
 }
 
 public function listQueue($messaging_url='')
 {
   if($messaging_url == '')
            $messaging_url = $this->messaging_url.$this->tenantId;
    
    $url = $messaging_url.'/queues';
 
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-Auth-Token: ' . $this->token,
    'Content-Type: ' . 'application/json' 
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    $response = curl_exec($ch);
    if($response === false)
    {
      echo 'Curl error: ' . curl_error($ch);die();
    }
    return $response;
 }

} 
 
?>


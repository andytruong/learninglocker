<?php namespace Controllers\xAPI;

use \Locker\Repository\Document\DocumentRepository as Document;
use Locker\Repository\Document\DocumentType as DocumentType;

class AgentController extends DocumentController
{
  /**
   * Construct
   *
   * @param DocumentRepository $document
   */
  public function __construct(Document $document)
  {
    parent::__construct($document);

    $this->document_type = DocumentType::AGENT;
    $this->document_ident = "profileId";

  }

  /**
   * Handle Single and Multiple GETs and CORS PUT/POST/DELETE requests
   * Return a list of stateId's based on activityId and actor match.
   *
   * @return Response
   */
  public function all()
  {
    $data = $this->checkParams(
      array(
        'agent' => array('string','json')
      ),
      array(
        'since'        => array('string', 'timestamp')
      ), $this->params
    );

    $documents = $this->document->all( $this->lrs->_id, $this->document_type, $data );

    //return array of only the stateId values for each document
    $ids = array_column($documents->toArray(), 'identId');

    return \Response::json( $ids );
  }


  /**
   * Single Document GET
   *
   * @return Response
   */
  public function get()
  {
    $data = $this->checkParams(
      array(
        'agent' => array('string','json'),
        'profileId'    => 'string'
      ),
      array(),
      $this->params
    );

    return $this->documentResponse( $data ); // use the DocumentController to handle document response
  }

  /**
   * Handle PUT and POST methods
   *
   * @return Response
   */
  public function store()
  {
    $data = $this->checkParams(
      array(
        'agent' => array('string','json'),
        'profileId'    => 'string'
      ),
      array(), $this->params
    );

    //Get the content from the request
    $data['content_info'] = $this->getAttachedContent('content');

    //Get the updated timestamp
    $updated = $this->getUpdatedValue();

    //Store the document
    $store = $this->document->store( $this->lrs->_id, $this->document_type, $data, $updated, $this->method );

    if ($store) {
      return \Response::json( array( 'ok', 204 ) );
    }

    return \Response::json( array( 'error', 400 ) );

  }

  /**
   * Handles routing to single document delete requests
   * Multiple document deletes are not permitted on activities
   *
   * @param  int  $id
   *
   * @return Response
   */
  public function delete()
  {
    $single_delete = isset($this->params[$this->document_ident]);

    if ($single_delete) { //single document delete
      $data = $this->checkParams(
        array(
          'agent' => array('string','json'),
          'profileId'    => 'string'
        ),
        array(), $this->params
      );
    } else {
      \App::abort(400, 'Multiple document DELETE not permitted');
    }

    $success = $this->document->delete( $this->lrs->_id, $this->document_type, $data, $single_delete );

    if ($success) {
      return \Response::json( array( 'ok', 204 ) );
    }

    return \Response::json( array( 'error', 400 ) );
  }

  /**
   * Search for and return a Person object
   *
   * @return Response
   **/
  public function search()
  {
    $data = $this->checkParams(
      array(
        'agent' => array('string','json')
      ),
      array(), $this->params
    );


    // @todo - Multiple Agent profiling into person
    // While the LRS doesn't have the ability to link agents (as an agent can only be defined by one identifier) we will simply return the queryied agent as a Person
    $agent = $data['agent'];

    $person = array(
      'objectType'  =>  'Person'
    );

    if ( isset($agent->name) ) {
      $person['name'] = array($agent->name);
    }

    if ( isset($agent->mbox) ) {
      $person['mbox'] = array($agent->mbox);
    }
    if ( isset($agent->mbox_sha1sum) ) {
      $person['mbox_sha1sum'] = array($agent->mbox_sha1sum);
    }
    if ( isset($agent->openid) ) {
      $person['openid'] = array($agent->openid);
    }
    if ( isset($agent->account) ) {
      $person['account'] = array($agent->account);
    }

    return \Response::json($person);

  }

}

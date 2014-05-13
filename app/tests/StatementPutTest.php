<?php

class StatementPutTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        Route::enableFilters();

        // Authentication as super user.
        $user = User::firstOrCreate(array('email' => 'quan@ll.com'));
        Auth::login($user);
        $this->createLRS();
    }

    private function _makeRequest($param)
    {
        return $this->call('PUT', '/data/xAPI/statements', array('statementId' => $param['id']), array(), array('PHP_AUTH_USER' => $this->lrs->api['basic_key'],
                'PHP_AUTH_PW' => $this->lrs->api['basic_secret'],
                'HTTP_X-Experience-API-Version' => '1.0.1'
                ), json_encode($param)
        );
    }

    /**
     * Create statements for lrs
     *
     * @return void
     */
    public function testPutBehavior()
    {
        $vs = $this->defaultStatment();
        $result = $this->createStatement($vs, $this->lrs);

        //case: conflict-matches
        $statement = App::make('Locker\Repository\Statement\EloquentStatementRepository');
        $createdStatement = $statement->find($result['ids'][0]);

        $param = array(
            'actor' => $createdStatement->statement['actor'],
            'verb' => $createdStatement->statement['verb'],
            'context' => $createdStatement->statement['context'],
            'object' => $createdStatement->statement['object'],
            'id' => $createdStatement->statement['id'],
            'timestamp' => $createdStatement->statement['timestamp'],
        );

        $response = $this->_makeRequest($param);
        $responseData = $response->getData();
        $responseStatus = $response->getStatusCode();
        $checkResponse = $responseStatus == 204 && empty($responseData);

        $this->assertTrue($checkResponse);

        /**
         * case: conflict nomatch
         * FAILURE:
         * break at EloquentStatementRepository->doesStatementIdExist
         * the result of this function always return: conflict-matches
         */
        $param['actor']['name'] = 'quanvm';
        $response = $this->_makeRequest($param);
        $checkResponse = $response->getStatusCode() == 409;
        $this->assertTrue($checkResponse);
    }

}

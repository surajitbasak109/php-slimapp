<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Valitron\Validator as validate;

// Get all customers
$app->get('/api/customers', function (Request $request, Response $response, array $args) {
  $customers = DB::select('customers', "*")
    ->fetchAll();
  $data = json_encode($customers);
  echo $data;
});

// Get single customer
$app->get('/api/customers/{id}', function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  $customer = DB::select_where('customers', '*', ['id' => $id]);
  if ($customer->rowCount() > 0) {
    $data = $customer->fetch();
    $newResponse = $response->withJson($data, 200);
  } else {
    $data = ['errors' => ['general_error' => 'Customer does not exist']];
    $newResponse = $response->withJson($data, 404);
  }

  return $newResponse;
});


// Add a customer
$app->post('/api/customers', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();

  $v = new validate($data);
  $v->rule('required', ['first_name', 'last_name', 'phone', 'email', 'address', 'city', 'state'])
    ->rule('email', 'email');
  if ($v->validate()) {
    DB::insert('customers', $data);
    $message = ['status' => true, 'message' => "Customer added", 'data' => $data];
    $newResponse = $response->withJson($message, 200);
  } else {
    $message = ['status' => false, 'errors' => $v->errors()];
    $newResponse = $response->withJson($message, 400);
  }
  return $newResponse;
});

// Update a customer
$app->put('/api/customers/{id}', function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  $data = $request->getParsedBody();

  if (!empty($data)) {
    DB::update('customers', ['id' => $id], $data);
    $customer = DB::select_where('customers', '*', ['id' => $id])
      ->fetch();
    $message = ['status' => true, 'message' => "Customer updated", 'data' => $customer];
    $newResponse = $response->withJson($message, 200);
  } else {
    $message = ['status' => false, 'errors' => ['general_error' => 'Nothing to update.']];
    $newResponse = $response->withJson($message, 400);
  }

  return $newResponse;
});


// Delete a customer
$app->delete('/api/customers/{id}', function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  $customer = DB::select_where('customers', '*', ['id' => $id])
    ->fetch();
  $deleted = DB::delete('customers', ['id' => $id]);

  if ($deleted) {
    echo json_encode(['status' => true, 'message' => "Customer deleted", 'data' => $customer]);
  } else {
    echo json_encode(['status' => false, 'errors' => ['general_error' => 'Unable to delete']]);
  }
});

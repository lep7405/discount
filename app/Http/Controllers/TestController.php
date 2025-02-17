<?php

namespace App\Http\Controllers;

use Exception;

class TestController extends Controller
{
    public function testException()
    {
        try {
            $this->makeSomeThingRisky();

            return response()->json(['message' => 'Success']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function makeSomeThingRisky()
    {
        throw new Exception('Test Exception');
    }
}

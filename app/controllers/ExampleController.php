<?php
use Phalcon\Http\Response;
namespace Controllers;

class ExampleController extends \Phalcon\Mvc\Controller {

	public static function testPingAction() {
	 return  ['sdf','sdf','sdf'];
	}

	public static function pingAction() {
		 return  ['ppppsdf','sdfsdfsdf','111111sdf'];
        //new Jete();
		/*return array(

            'book' => array(
                array(
                    '@attributes' => array(
                        'author' => 'George Orwell'
                    ),
                    'title' => '1984'
                ),
                array(
                    '@attributes' => array(
                        'author' => 'Isaac Asimov'
                    ),
                    'title' => 'Foundation',
                    'price' => '$15.61'
                ),
                array(
                    '@attributes' => array(
                        'author' => 'Robert A Heinlein'
                    ),
                    'title' => 'Stranger in a Strange Land',
                    'price' => array(
                        '@attributes' => array(
                            'discount' => '10%'
                        ),
                        '@value' => '$18.00'
                    )
                )
            )
        );*/
	}

}

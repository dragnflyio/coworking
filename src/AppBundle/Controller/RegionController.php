<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Route("/region")
 */
class RegionController extends Controller
{
  /**
   * @Route("/get-json", name = "region_get_json")
   */
  public function getRegionAction(){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM region");
    $statement->execute();
    $rows = $statement->fetchAll();
    $regions = array();
    foreach ($rows as $region) {
      $regions[] = array($region['id'], $region['name']);
    }

    $response = new Response(
      json_encode($regions),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }
}

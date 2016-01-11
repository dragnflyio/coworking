<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Unit;
use AppBundle\Entity\PageUnit;
use AppBundle\Form\Type\PageUnitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UnitController extends Controller
{
  /**
   * @Route("/unit", name="unit")
   */
  public function unitAction(Request $request)
  {
    // this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
    $pageUnit = new PageUnit();
    $pageUnitOld = new PageUnit();
    $em = $this->getDoctrine()->getManager();
    $repository = $this->getDoctrine()
    ->getRepository('AppBundle:Unit');
    $units = $repository->findAll();

    if (!empty($units)) {
      foreach ($units as $unit) {
        $pageUnit->getUnits()->add($unit);
        $pageUnitOld->getUnits()->add($unit);
      }
    }

    $form = $this->createForm(PageUnitType::class, $pageUnit);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {

      $data_form = $form->getData();
      $data_units = $data_form->getUnits()->getValues();
      // Add new unit & update unit.
      foreach ($data_units as $unit) {
        $id = $unit->id;
        if ($this->checkUnitExisting($id) == false) {
          $this->addUnit($em, $unit);
        }
        else {
          $this->updateUnit($em, $id, $unit);
        }
      }
      $em->flush();
      // Delete unit.
      $units_old = $pageUnitOld->getUnits()->getValues();
      foreach ($units_old as $unit) {
        if (false === $pageUnit->getUnits()->contains($unit)) {
          $this->deleteUnit($em, $unit);
        }
      }

      $this->get('session')->getFlashBag()->add(
        'notice',
        'Unit is added!'
      );
    }

    return $this->render('unit/unit.html.twig', array(
      'form' => $form->createView(),
      'errors' => $form->getErrors(),
    ));
  }

  /**
   * Function update unit by id.
   * @param: $id, $data
   */
  private function updateUnit($em, $id, $data)
  {
    $unit = $em->getRepository('AppBundle:Unit')->find($id);
    $unit->setNameen($data->nameen);
    $unit->setNamevi($data->namevi);
    $unit->setValue($data->value);
    $unit->setBaseUnit($data->base_unit);
    $em->flush();
  }

  /**
   * Function add unit.
   */
  private function addUnit($em, $data)
  {
    $unit = new Unit();
    $unit->setId($data->id);
    $unit->setNameen($data->nameen);
    $unit->setNamevi($data->namevi);
    $unit->setValue($data->value);
    $unit->setBaseUnit($data->base_unit);
    $em->persist($unit);
  }

  /**
   * Function delete unit.
   */
  private function deleteUnit($em, Unit $unit)
  {
    $em->remove($unit);
    $em->flush();
  }

  /**
   * Function check unit existing.
   */
  private function checkUnitExisting($id)
  {
    $unit = $this->getDoctrine()
      ->getRepository('AppBundle:Unit')
      ->find($id);
    if (!$unit) {
      return false;
    }
    else {
      return true;
    }
  }
}

<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;

class PageUnitType extends AbstractType
{
  /**
   * Build form manage units, @form save, reset, units data.
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('save', SubmitType::class, array('label' => 'Save', 'attr' => array('class' => 'btn btn-primary')));
    $builder->add('reset', ResetType::class, array('label' => 'Reset', 'attr' => array('class' => 'btn btn-warning')));
    $builder->add('units', CollectionType::class, array(
      'entry_type' => UnitType::class,
      'allow_add'    => true,
      'allow_delete' => true,
    ));
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'AppBundle\Entity\PageUnit',
    ));
  }
}

<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnitType extends AbstractType
{
  /**
   * Build form unit item, @form: id, nameen, namevi, value.
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('id');
    $builder->add('nameen');
    $builder->add('namevi');
    $builder->add('value');
    $builder->add('base_unit');
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'AppBundle\Entity\Unit',
    ));
  }
}

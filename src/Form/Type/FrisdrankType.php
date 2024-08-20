<?php 
declare(strict_types = 1);

namespace App\Form\Type;

use App\Entity\Frisdrank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrisdrankType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prijs', NumberType::class)
            ->add('aantal', NumberType::class)
            ->add('wijzigen', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Frisdrank::class,
        ]);
    }
}
?>
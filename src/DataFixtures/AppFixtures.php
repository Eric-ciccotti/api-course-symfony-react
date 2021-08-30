<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * Hasher (encodeur de mot de passe)
     *
     * @var UserPasswordHasherInterface
     */
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');


        //pour chaque faux utilisateur
        for ($u = 0; $u < 10; $u++) {
            $user = new User();

            //compteur pour les factures, c'est juste le numéro de la facture
            $chrono = 1;

            //encodeur de mot de passe pour l'utilisateur
            $hash = $this->hasher->hashPassword($user, "password");

            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->email())
                ->setPassword($hash);

            $manager->persist($user);

            //pour chaque utilsateur on lui crée des clients
            for ($c = 0; $c < mt_rand(5, 20); $c++) {
                $customer = new Customer();
                $customer->setFirstName($faker->firstName())
                    ->setLastName($faker->lastName)
                    ->setCompany($faker->company)
                    ->setEmail($faker->email)
                    ->setUser($user);

                $manager->persist($customer);

                //chaque client de l'utilisateur a une facture
                for ($i = 0; $i < mt_rand(3, 10); $i++) {
                    $invoice = new Invoice();
                    $invoice->setAmount($faker->randomFloat(2, 250, 5000))
                        ->setSentAt($faker->dateTimeBetween('-6 months'))
                        ->setStatus($faker->randomElement(['SENT', 'PAID', 'CANCELLED']))
                        ->setCustomer($customer)
                        ->setChrono($chrono);

                    $chrono++;

                    $manager->persist($invoice);
                }
            }
        }



        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}

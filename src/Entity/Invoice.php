<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\InvoiceRepository;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\InvoiceIncrementController;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

/**
 * @ORM\Entity(repositoryClass=InvoiceRepository::class)
 * @ApiResource(attributes={
 *      "pagination_enabled"=true,
 *      "pagination_items_per_page"=20,
 *      "order":{"amount":"desc"}
 * },
 * subresourceOperations={
 *      "api_customers_invoices_get_subresource"={
 *          "normalization_context"={"groups"="invoices_subresource"}
 *      }
 * },
 * itemOperations={"GET","PUT","PATCH","DELETE",
 *          "increment"={
 *              "method"="POST", 
 *              "path"="/invoices/{id}/increment", 
 *              "controller"="App\Controller\InvoiceIncrementalController",
 *              "openapi_context"={
 *                  "summary":"fonction increment",
 *                  "description":"increment un chrono"}
 *             }
 * },
 * denormalizationContext={"disable_type_enforcement"=false},
 * normalizationContext={
 *          "groups"={"invoices_read"}
 * }),
 * @ApiFilter(OrderFilter::class)
 */
class Invoice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"invoices_read","customers_read","invoices_subresource"})
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Groups({"invoices_read","customers_read","invoices_subresource"})
     * @Assert\NotBlank(message="Le montant de la facture est obligatoire")
     * @Assert\Type(type="numeric",message="Le montant de la facture doit être un numérique")
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"invoices_read","customers_read","invoices_subresource"})
     * @Assert\DateTime(message="la date doit etre au format YYYY-MM-DD")
     * @Assert\NotBlank(message="La date d'envoie doit être renseignée")
     */
    private $sentAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"invoices_read","customers_read","invoices_subresource"})
     * @Assert\NotBlank(message="Le statut de la facture est obligatoire") 
     * @Assert\Choice={"SENT","PAID","CANCELLED"}, message="Le statut doit etre SEND , PAID ou CANCELLED")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Le client de la facture doit être renseigné") 
     * @Groups({"invoices_read"})
     */
    private $customer;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="il faut un chrono pour la facture") 
     * @Assert\Type(type="integer",message="le chrono doit être un nombre")
     * @Groups({"invoices_read","customers_read","invoices_subresource"})
     */
    private $chrono;

    /**
     * Permet de récupérer le User à qui appartient la facture
     * @Groups({"invoices_read","invoices_subresource"})
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->customer->getUser();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTime $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getChrono(): ?int
    {
        return $this->chrono;
    }

    public function setChrono($chrono): self
    {
        $this->chrono = $chrono;

        return $this;
    }
}

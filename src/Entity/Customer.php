<?php

namespace App\Entity;

use App\Entity\Invoice;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CustomerRepository;
use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 * @ApiResource(
 *      collectionOperations={"GET"={"path"="/clients"},"POST"},
 *      itemOperations={"GET"={"path"="/clients/{id}"},"PUT","PATCH","DELETE"},
 *      subresourceOperations={
 *          "invoices_get_subresource"={"path"="/clients/{id}/factures"}
 *       },
 *      normalizationContext={
 *          "groups"={"customers_read"}
 * })
 * @ApiFilter(SearchFilter::class)
 * @ApiFilter(OrderFilter::class)
 * 
 */
class Customer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"customers_read","invoices_read"})
     */
    private $id;

    /**
     * @Groups({"customers_read","invoices_read"})
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @Groups({"customers_read","invoices_read"})
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @Groups({"customers_read","invoices_read"})
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @Groups({"customers_read","invoices_read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @Groups({"customers_read"})
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="customer")
     * @ApiSubresource()
     */
    private $invoices;

    /**
     * @Groups({"customers_read"})
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="customers")
     */
    private $user;

    /**
     * Permet de récuperer le total des factures (invoices)
     * Montant que mon client doit payé
     * @Groups({"customers_read"})
     * @return float
     */
    public function getTotalAmount(): float
    {
        return array_reduce($this->invoices->toArray(), function ($total, $invoice) {
            return $total + $invoice->getAmount();
        }, 0);
    }

    /**
     * Permet de récuperer le total des factures non payées (hors factures payées OU annulée)
     * Montant que mon client n'a pas encore payé
     * @Groups({"customers_read"})
     * @return float
     */
    public function getUnpaidAmount(): float
    {
        return array_reduce($this->invoices->toArray(), function ($total, $invoice) {
            return $total + ($invoice->getStatus() === "PAID" || $invoice->getStatus() === "CANCELLED" ? 0 :
                $invoice->getAmount());
        }, 0);
    }

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setCustomer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getCustomer() === $this) {
                $invoice->setCustomer(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}

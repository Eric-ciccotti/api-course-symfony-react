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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 * @ApiResource(
 *      collectionOperations={"GET"={"path"="/customers"},"POST"},
 *      itemOperations={"GET"={"path"="/customers/{id}"},"PUT","PATCH","DELETE"},
 *      subresourceOperations={
 *          "invoices_get_subresource"={"path"="/customers/{id}/invoices"}
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
     * @Groups({"customers_read","invoices_read","invoices_subresource"})
     */
    private $id;

    /**
     * @Groups({"customers_read","invoices_read","invoices_subresource"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le prenom du customer est obligatoire")
     * @Assert\Length(min=3, minMessage="le prenom doit faire entre 3 et 255 caractères!")
     * @Assert\Length(max=255, maxMessage="le prenom doit faire entre 3 et 255 caractères!")
     * )
     */
    private $firstName;

    /**
     * @Groups({"customers_read","invoices_read","invoices_subresource"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom de famille du customer est obligatoire")
     * @Assert\Length(min=3, minMessage="le nom de famille doit faire entre 3 et 255 caractères!")
     * @Assert\Length(max=255, maxMessage="le nom de famille doit faire entre 3 et 255 caractères!")
     */
    private $lastName;

    /**
     * @Groups({"customers_read","invoices_read","invoices_subresource"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="L'email du customer est obligatoire")
     * @Assert\Email(message="le format de l'email doit être valide")
     */
    private $email;

    /**
     * @Groups({"customers_read","invoices_read","invoices_subresource"})
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
     * @Assert\NotBlank(message="L'utilisateur est obligatoire")
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

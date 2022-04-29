<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\Cache;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;

/**
 * @Cache
 * @Entity
 * @Table("cache_city")
 */
#[ORM\Entity, ORM\Table(name: 'cache_city'), ORM\Cache]
class City
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    protected $id;

    /**
     * @var string
     * @Column(unique=true)
     */
    #[ORM\Column(unique: true)]
    protected $name;

    /**
     * @var State|null
     * @Cache
     * @ManyToOne(targetEntity="State", inversedBy="cities")
     * @JoinColumn(name="state_id", referencedColumnName="id")
     */
    #[ORM\Cache]
    #[ORM\ManyToOne(targetEntity: 'State', inversedBy: 'citities')]
    #[ORM\JoinColumn(name: 'state_id', referencedColumnName: 'id')]
    protected $state;

    /**
     * @var Collection<int, Travel>
     * @ManyToMany(targetEntity="Travel", mappedBy="visitedCities")
     */
    #[ORM\ManyToMany(targetEntity: 'Travel', mappedBy: 'visitedCities')]
    public $travels;

    /**
     * @psalm-var Collection<int, Attraction>
     * @Cache
     * @OrderBy({"name" = "ASC"})
     * @OneToMany(targetEntity="Attraction", mappedBy="city")
     */
    #[ORM\Cache, ORM\OrderBy(['name' => 'ASC'])]
    #[ORM\OneToMany(targetEntity: 'Attraction', mappedBy: 'city')]
    public $attractions;

    public function __construct(string $name, ?State $state = null)
    {
        $this->name        = $name;
        $this->state       = $state;
        $this->travels     = new ArrayCollection();
        $this->attractions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(State $state): void
    {
        $this->state = $state;
    }

    public function addTravel(Travel $travel): void
    {
        $this->travels[] = $travel;
    }

    /**
     * @psalm-return Collection<int, Travel>
     */
    public function getTravels(): Collection
    {
        return $this->travels;
    }

    public function addAttraction(Attraction $attraction): void
    {
        $this->attractions[] = $attraction;
    }

    /**
     * @psalm-return Collection<int, Attraction>
     */
    public function getAttractions(): Collection
    {
        return $this->attractions;
    }

    public static function loadMetadata(ClassMetadata $metadata): void
    {
        $metadata->setInheritanceType(ClassMetadata::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'cache_city']);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_IDENTITY);
        $metadata->setChangeTrackingPolicy(ClassMetadata::CHANGETRACKING_DEFERRED_IMPLICIT);

        $metadata->enableCache(
            [
                'usage' => ClassMetadata::CACHE_USAGE_READ_ONLY,
            ]
        );

        $metadata->mapField(
            [
                'fieldName' => 'id',
                'type' => 'integer',
                'id' => true,
            ]
        );

        $metadata->mapField(
            [
                'fieldName' => 'name',
                'type' => 'string',
            ]
        );

        $metadata->mapOneToOne(
            [
                'fieldName'      => 'state',
                'targetEntity'   => State::class,
                'inversedBy'     => 'cities',
                'joinColumns'    =>
                    [
                        [
                            'name' => 'state_id',
                            'referencedColumnName' => 'id',
                        ],
                    ],
            ]
        );
        $metadata->enableAssociationCache('state', [
            'usage' => ClassMetadata::CACHE_USAGE_READ_ONLY,
        ]);

        $metadata->mapManyToMany(
            [
                'fieldName' => 'travels',
                'targetEntity' => Travel::class,
                'mappedBy' => 'visitedCities',
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName' => 'attractions',
                'targetEntity' => Attraction::class,
                'mappedBy' => 'city',
                'orderBy' => ['name' => 'ASC'],
            ]
        );
        $metadata->enableAssociationCache('attractions', [
            'usage' => ClassMetadata::CACHE_USAGE_READ_ONLY,
        ]);
    }
}

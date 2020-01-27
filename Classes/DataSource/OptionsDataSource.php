<?php
namespace NeosRulez\ShoppingCart\DataSource;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class OptionsDataSource extends AbstractDataSource
{

    /**
     * @var string
     */
    static protected $identifier = 'neosrulez-shoppingcart-options';

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\OptionRepository
     */
    protected $optionRepository;

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\OptionvalueRepository
     */
    protected $optionvalueRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @param NodeInterface $node The node that is currently edited (optional)
     * @param array $arguments Additional arguments (key / value)
     * @return array
     */
    public function getData(NodeInterface $node = null, array $arguments)
    {
        $options = [];
        foreach ($this->optionRepository->findAll() as $option) {
            $options[$this->persistenceManager->getIdentifierByObject($option)] = ['label' => $option->getName()];
        }
        return $options;
    }
}

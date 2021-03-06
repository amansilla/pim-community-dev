<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Model\Write;

use Akeneo\Apps\Domain\Model\ValueObject\AppCode;
use Akeneo\Apps\Domain\Model\ValueObject\AppLabel;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class App
{
    /** @var AppCode */
    private $code;

    /** @var AppLabel */
    private $label;

    /** @var FlowType */
    private $flowType;

    /** @var ClientId */
    private $clientId;

    /** @var UserId */
    private $userId;

    public function __construct(
        string $code,
        string $label,
        string $flowType,
        int $clientId,
        UserId $userId
    ) {
        $this->code = new AppCode($code);
        $this->label = new AppLabel($label);
        $this->flowType = new FlowType($flowType);
        $this->clientId = new ClientId($clientId);
        $this->userId = $userId;
    }

    public function code(): AppCode
    {
        return $this->code;
    }

    public function label(): AppLabel
    {
        return $this->label;
    }

    public function flowType(): FlowType
    {
        return $this->flowType;
    }

    public function clientId(): ClientId
    {
        return $this->clientId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function setLabel(AppLabel $label): void
    {
        $this->label = $label;
    }

    public function setFlowType(FlowType $flowType): void
    {
        $this->flowType = $flowType;
    }
}

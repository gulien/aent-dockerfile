<?php

namespace TheAentMachine\AentDockerfile\Event;

use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StringsException;
use Symfony\Component\Filesystem\Filesystem;
use TheAentMachine\Aent\Context\Context;
use TheAentMachine\Aent\Event\Builder\AbstractNewImageEvent;
use TheAentMachine\Aent\Payload\Builder\NewImageReplyPayload;
use TheAentMachine\Aenthill\Pheromone;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use TheAentMachine\Service\Service;
use function \Safe\sprintf;
use function \Safe\chown;
use function \Safe\chgrp;

final class NewImageEvent extends AbstractNewImageEvent
{
    /**
     * @param Service $service
     * @return NewImageReplyPayload
     * @throws FilesystemException
     * @throws MissingEnvironmentVariableException
     * @throws StringsException
     */
    protected function createDockerfile(Service $service): NewImageReplyPayload
    {
        /** @var Context $context */
        $context = Context::fromMetadata();
        $commands = \implode(PHP_EOL, $service->getDockerfileCommands()) . PHP_EOL;
        $dockerfileName = sprintf("Dockerfile.%s.%s", $context->getEnvironmentName(), $service->getServiceName());
        $dockerfilePath = Pheromone::getContainerProjectDirectory() . "/$dockerfileName";
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($dockerfilePath, $commands);
        $dirInfo = new \SplFileInfo(\dirname($dockerfilePath));
        chown($dockerfilePath, $dirInfo->getOwner());
        chgrp($dockerfilePath, $dirInfo->getGroup());
        return new NewImageReplyPayload($dockerfileName);
    }
}

<?php

namespace App\JsonApi\Specials;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'specials';

    /**
     * @param \App\Model\Special $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    /**
     * @param \App\Model\Special $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        return [
            // 'createdAt' => $resource->created_at,
            // 'updatedAt' => $resource->updated_at,
            'spe' => $resource->spe,
        ];
    }

    public function getRelationships($resource, $isPrimary, array $includeRelationships)
    {
        return [
            // 'courses' => [
            //     self::SHOW_SELF => false,
            //     self::SHOW_RELATED => false,
            //     self::SHOW_DATA => isset($includeRelationships['courses']),
            //     self::DATA => function () use ($resource) {
            //         return $resource->courses;
            //     },
            // ]
        ];
    }
}

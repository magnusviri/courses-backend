<?php

namespace App\JsonApi\Attrs;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'attrs';

    /**
     * @param \App\Attr $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    /**
     * @param \App\Attr $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        return [
//            'createdAt' => $resource->created_at,
//            'updatedAt' => $resource->updated_at,
            'attr' => $resource->attr,
            'desc' => $resource->desc,
        ];
    }

    public function getRelationships($resource, $isPrimary, array $includeRelationships)
    {
        return [
            // 'courses' => [
            //     self::SHOW_SELF => false,
            //     self::SHOW_RELATED => false,
            // ]
        ];
    }
}

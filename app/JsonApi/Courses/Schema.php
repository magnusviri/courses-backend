<?php

namespace App\JsonApi\Courses;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'courses';

    /**
     * @param \App\Course $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    /**
     * @param \App\Course $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        return [
//            'createdAt' => $resource->created_at,
            'updatedAt' => $resource->updated_at,
            'cat' => $resource->cat,
            'sec' => $resource->sec,
            'com' => $resource->com,
            'sub' => $resource->sub,
            'num' => $resource->num,
            'nam' => $resource->nam,
            'enr' => $resource->enr,
            'des' => $resource->des,
            'cap' => $resource->cap,
            'typ' => $resource->typ,
            'uni' => $resource->uni,
            'yea' => $resource->yea,
            'sem' => $resource->sem,
            'fee' => $resource->fee,
            'rek' => $resource->rek,
            'syl' => $resource->syl,
        ];
    }

//     public function getIncludePaths()
//     {
//         return ['instructors', 'attrs'];
//     }

    public function getRelationships($resource, $isPrimary, array $includeRelationships)
    {
        return [
            'attrs' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => false,
//                self::SHOW_DATA => isset($includeRelationships['attrs']),
//                self::DATA => function () use ($resource) {
//                    return $resource->attrs;
//                },

            ],
            'instructors' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => false,
//                self::SHOW_DATA => isset($includeRelationships['instructors']),
//                self::DATA => function () use ($resource) {
//                    return $resource->instructors;
//                },
            ]
        ];
    }
}

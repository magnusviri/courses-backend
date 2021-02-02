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
            'req' => $resource->req,
            'sea' => $resource->sea,
            'tba' => $resource->tba,
            'wai' => $resource->wai,
        ];
    }

    public function getRelationships($resource, $isPrimary, array $includeRelationships)
    {
        return [
            'attrs' => [
                self::SHOW_SELF => false,
                self::SHOW_RELATED => false,
                self::SHOW_DATA => isset($includeRelationships['attrs']),
                self::DATA => function () use ($resource) {
                    return $resource->attrs;
                },
            ],
            'description' => [
                self::SHOW_SELF => false,
                self::SHOW_RELATED => false,
                self::SHOW_DATA => isset($includeRelationships['description']),
                self::DATA => function () use ($resource) {
                    return $resource->description;
                },
            ],
            'instructors' => [
                self::SHOW_SELF => false,
                self::SHOW_RELATED => false,
                self::SHOW_DATA => isset($includeRelationships['instructors']),
                self::DATA => function () use ($resource) {
                    return $resource->instructors;
                },
            ],
            'meets_with' => [
                self::SHOW_SELF => false,
                self::SHOW_RELATED => false,
                self::SHOW_DATA => isset($includeRelationships['meets_with']),
                self::DATA => function () use ($resource) {
                    return $resource->meetsWith;
                },
            ],
            'special' => [
                self::SHOW_SELF => false,
                self::SHOW_RELATED => false,
                self::SHOW_DATA => isset($includeRelationships['special']),
                self::DATA => function () use ($resource) {
                    return $resource->special;
                },
            ],
            'when_where' => [
                self::SHOW_SELF => false,
                self::SHOW_RELATED => false,
                self::SHOW_DATA => isset($includeRelationships['when_where']),
                self::DATA => function () use ($resource) {
                    return $resource->whenWhere;
                },
            ],
        ];
    }
}

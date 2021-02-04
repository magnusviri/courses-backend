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
            'cap' => $resource->cap,
            'cat' => $resource->cat,
            'com' => $resource->com,
            // 'des' => $resource->des,
            'enr' => $resource->enr,
            'fee' => $resource->fee,
            'nam' => $resource->nam,
            'num' => $resource->num,
            // 'rek' => $resource->rek,
            'req' => $resource->req,
            // 'sea' => $resource->sea,
            'sec' => $resource->sec,
            'sem' => $resource->sem,
            'sub' => $resource->sub,
            'syl' => $resource->syl,
            'typ' => $resource->typ,
            'uni' => $resource->uni,
            'wai' => $resource->wai,
            'yea' => $resource->yea,
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
                self::SHOW_DATA => (isset($includeRelationships['description']) && $resource->description),
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
                self::SHOW_DATA => (isset($includeRelationships['meets_with']) && sizeof($resource->meetsWith) > 0),
                self::DATA => function () use ($resource) {
                    return $resource->meetsWith;
                },
            ],
            'special' => [
                self::SHOW_SELF => false,
                self::SHOW_RELATED => false,
                self::SHOW_DATA => (isset($includeRelationships['special']) && $resource->special),
                self::DATA => function () use ($resource) {
                    return $resource->special;
                },
            ],
            'when_where' => [
                self::SHOW_SELF => false,
                self::SHOW_RELATED => false,
                self::SHOW_DATA => (isset($includeRelationships['when_where']) && sizeof($resource->whenWhere) > 0),
                self::DATA => function () use ($resource) {
                    return $resource->whenWhere;
                },
            ],
        ];
    }

    public function getResourceLinks($resource) {
        return null;
    }
}

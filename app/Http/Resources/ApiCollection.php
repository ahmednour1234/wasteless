<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ApiCollection extends ResourceCollection
{
    /**
     * The class name of the resource that this resource collects.
     * Laravel uses $collects to wrap each item.
     * @var string
     */
    public $collects;

    /**
     * Create a new resource collection instance.
     *
     * @param  mixed   $resource        The collection or paginator
     * @param  string  $resourceClass   Fully-qualified class name of a JsonResource
     * @return void
     */
    public function __construct($resource, string $resourceClass)
    {
        parent::__construct($resource);
        $this->collects = $resourceClass;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // 'data' wrapping is handled by ResourceCollection
        return parent::toArray($request);
    }

    /**
     * Add pagination meta and links when applicable.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        if ($this->resource instanceof LengthAwarePaginator) {
            return [
                'meta' => [
                    'current_page' => $this->resource->currentPage(),
                    'per_page'     => $this->resource->perPage(),
                    'last_page'    => $this->resource->lastPage(),
                    'total'        => $this->resource->total(),
                ],
                'links' => [
                    'first' => $this->resource->url(1),
                    'last'  => $this->resource->url($this->resource->lastPage()),
                    'prev'  => $this->resource->previousPageUrl(),
                    'next'  => $this->resource->nextPageUrl(),
                ],
            ];
        }

        // non-paginated collections return only 'data'
        return [];
    }
}

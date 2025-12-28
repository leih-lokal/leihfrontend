<?php

use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Uuid\Uuid;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;

class LeihlokalPage extends Page {
    public function children(): Pages
    {
        if ($this->children instanceof Pages) {
            return $this->children;
        }

        // 1. Get real file-based children first (from content/1_ll/)
        $realChildren = [];
        try {
            $realChildrenPages = parent::children();
            foreach ($realChildrenPages as $child) {
                $realChildren[] = [
                    'slug'     => $child->slug(),
                    'num'      => $child->num(),
                    'template' => $child->intendedTemplate(),
                    'model'    => $child->intendedTemplate(),
                    'content'  => $child->content()->toArray()
                ];
            }
        } catch (Exception $e) {
            // If there's an error getting real children, continue with just virtual ones
        }

        // 2. Get virtual children from PocketBase API
        $results = [];
        $virtualPages = [];

        // PocketBase pagination: fetch all items across all pages
        // Default perPage is 30, max is 500. We'll use 500 to minimize requests.
        $perPage = 500;
        $currentPage = 1;
        $totalPages = 1;

        // Loop through all pages to get ALL items
        do {
            $url = 'https://buergerstiftung-karlsruhe.de/api/collections/item_public/records?page=' . $currentPage . '&perPage=' . $perPage;
            $request = Remote::get($url);

            if ($request->code() === 200) {
                $data = $request->json(false);

                // On first request, get total number of pages
                if ($currentPage === 1) {
                    $totalPages = $data->totalPages ?? 1;
                }

                // Merge items from this page
                if (isset($data->items) && is_array($data->items)) {
                    $results = array_merge($results, $data->items);
                }
            } else {
                // If request fails, stop pagination
                break;
            }

            $currentPage++;
        } while ($currentPage <= $totalPages);

        // Start virtual page numbering after real pages
        $virtualStartNum = count($realChildren) + 1;

        foreach ($results as $key => $item) {
            // Generate stable UUID based on item's iid
            $uuid = 'item-' . $item->iid;

            // Pad iid with leading zeros to preserve format (e.g., 5 -> 0005)
            $paddedIid = str_pad($item->iid, 4, '0', STR_PAD_LEFT);

            $virtualPages[] = [
                'slug'     => $paddedIid,
                'num'      => $virtualStartNum + $key,
                'template' => 'item',
                'model'    => 'item',
                'content'  => [
                    'title'          => $item->name ?? 'Untitled',
                    'iid'            => $item->iid ?? '',
                    'recordid'       => $item->id ?? '',  // PocketBase record ID for image URLs
                    'name'           => $item->name ?? '',
                    'brand'          => $item->brand ?? '',
                    'itemmodel'      => $item->model ?? '',
                    'category'       => is_array($item->category) ? implode(', ', $item->category) : ($item->category ?? ''),
                    'description'    => $item->description ?? '',
                    'availability'   => $item->status ?? 'unknown',
                    'deposit'        => $item->deposit ?? 0,
                    'copies'         => $item->copies ?? 1,
                    'parts'          => $item->parts ?? 1,
                    'itemimages'     => (isset($item->images) && is_array($item->images)) ? implode(',', $item->images) : '',
                    'manual'         => $item->manual ?? '',
                    'packaging'      => $item->packaging ?? '',
                    'synonyms'       => $item->synonyms ?? '',
                    'internal_note'  => $item->internal_note ?? '',
                    'highlight_color' => $item->highlight_color ?? '',
                    'added_on'       => $item->added_on ?? '',
                    'updated'        => $item->updated ?? '',
                    'uuid'           => $uuid,
                ]
            ];
        }

        // 3. Merge real and virtual children (real pages first, then virtual)
        $allPages = array_merge($realChildren, $virtualPages);

        return $this->children = Pages::factory($allPages, $this);
    }
}
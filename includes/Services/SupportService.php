<?php

namespace SupportMonitor\App\Services;

class SupportService extends Service {

    /**
     * @param $plugin
     * @param int $hour
     * @return array
     * @throws \Exception
     */
    public function get_unresolved_issues( $plugin, $hour = 24 ) {
        $url = 'https://wordpress.org/support/plugin/' . $plugin . '/feed';
        $args_for_get = [
            'timeout' => 20,
        ];

        $response = wp_remote_get( $url, $args_for_get );
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return [
                'issues' => [],
                'slug' => $plugin,
                'status' => 'failed',
                'message' => 'Data Fetch Error',
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        $xml = simplexml_load_string( $body, null, LIBXML_NOCDATA );
        if ( ! $xml ) {
            return [
                'issues' => [],
                'slug' => $plugin,
                'status' => 'failed',
                'message' => 'Data Fetch Error',
            ];
        }

        $ns = $xml->getNamespaces( true );

        $time_range = ( new \DateTime( 'NOW' ) )->setTimezone( new \DateTimeZone( 'UTC' ) );
        $time_range->sub( new \DateInterval( 'PT' . $hour . 'H' ) );

        $items = $xml->channel->item;
        $plugin_title = strval( $xml->channel->title );
        $issues = [];

        foreach ( $items as $item ) {
            $doc = new \DOMDocument();
            $doc->loadHTML( $item->description );
            $replies_text = $doc->getElementsByTagName( 'p' )->item( 0 )->textContent;
            $doc->loadHTML( $item->title );
            $resolve_title_exists = $doc->getElementsByTagName( 'span' )->length;
            if ( $resolve_title_exists ) {
                continue;
            }

            $replies_text_array = explode( ' ', $replies_text );
            $pub_date = ( new \DateTime( strval( $item->pubDate ) ) )->setTimezone( new \DateTimeZone( 'UTC' ) ); // phpcs:ignore

            if ( $replies_text_array[1] > 0 || $pub_date < $time_range ) {
                continue;
            }

            $issues[] = [
                'link' => strval( $item->link ),
                'title' => strval( $item->title ),
                'pubDate' => strval( $item->pubDate ), // phpcs:ignore
                'creator' => strval( $item->children( $ns['dc'] )->creator ),
                'replies' => $replies_text_array[1],
            ];
        }

        usort( $issues, array( $this, 'issueCompareByTimeStamp' ) );
        $issues = array_reverse( $issues );

        $data = [
            'issues' => $issues,
            'slug' => $plugin,
            'status' => 'success',
            'message' => count( $issues ) > 0 ? 'Success' : 'No Data Available',
        ];

        return $data;
    }

    /**
     * @param $issue1
     * @param $issue2
     * @return int
     */
    private function issueCompareByTimeStamp( $issue1, $issue2 ) {
        if ( strtotime( $issue1['pubDate'] ) < strtotime( $issue2['pubDate'] ) ) {
            return 1;
        } elseif ( strtotime( $issue1['pubDate'] ) > strtotime( $issue2['pubDate'] ) ) {
            return -1;
        } else {
			return 0;
        }
    }
}

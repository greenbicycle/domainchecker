<?php namespace Greenbicycle;

/**
 * DomainChecker class
 *
 * This is a quick class that I wrote for a project involving 
 * checking DNS records for 40+ domains at once on a regular basis.
 * 
 */

class DomainChecker
{

    protected $_domain;
    /**
     *  All dns records, unsorted
     */
    protected $_dns_all = array();

    /**
     * All dns records arranged in a multidimensional 
     * array by type.
     */
    protected $_dns_sorted = array();

    public function __construct($domain)
    {
        $this->_domain = $domain;
        $this->dns_all();
        $this->dns_sort();
        // This will be false if no records were found.
        return $this->_dns_all;
    }

    public function domain()
    {
        return $this->_domain;
    }

    /**
     *  For dns_get_record, type can be any one of the following:
     *      DNS_A, DNS_CNAME, DNS_HINFO, DNS_MX, DNS_NS, DNS_PTR, DNS_SOA, 
     *      DNS_TXT, DNS_AAAA, DNS_SRV, DNS_NAPTR, DNS_A6, DNS_ALL or DNS_ANY.
     *      (DNS_ALL is better than DNS_ANY, according to php.net)
     *
     * @params const one of the above
     *
     * @return array of arrays.
     *
     */
    public function dns_get($type)
    {
        return dns_get_record($this->_domain, $type);
    }

    /**
     * Get all dns records and store them
     *
     * It might be faster in the future to find
     * the records in dns_all instead of using multiple
     * calls to dns_get_record.
     */
    public function dns_all()
    {
        // Check to see if this has already been done.
        if ( is_array($this->_dns_all) && count($this->_dns_all) == 0 ){
            $this->_dns_all = $this->dns_get(DNS_ALL);
        }
        if ( count($this->_dns_all) == 0) {
            $this->_dns_all = false;
        }
        return $this->_dns_all;
    }

    /**
     * separate the records out by type
     */
    public function dns_sort() {
        if ( is_array($this->_dns_all)) {
            foreach( $this->_dns_all as $dns_record){
                // just to clean up the code a little
                $current_type = $dns_record['type'];
                if (!isset($this->_dns_sorted[$current_type])){
                    $this->_dns_sorted[$current_type] = array();
                }
                $this->_dns_sorted[$current_type][] = $dns_record;
            }
        } else {
            return false;
        }
    }

    public function dns_all_raw(){
        return $this->_dns_all;
    }

    public function dns_sorted_raw(){
        return $this->_dns_sorted;
    }

    public function nameservers() {
        return $this->_dns_sorted['NS'];
    }

    /**
     *
     * If there are no records, then return false.
     * If there are records, but no NS records, return false
     *
     */
    public function check()
    {
        if ($this->_dns_all === false) {
            return false;
        } elseif(!isset($this->_dns_sorted['ns'])) {
            // We should probably have a nameserver set
            return false;
        } else {
            return true;
        }

    }
}


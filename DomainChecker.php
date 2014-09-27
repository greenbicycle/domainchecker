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

    protected $domain;
    /**
     *  All dns records, unsorted
     */
    protected $dns_all = array();

    /**
     * All dns records arranged in a multidimensional 
     * array by type.
     */
    protected $dns_sorted = array();

    public function __construct($domain)
    {
        $this->domain = $domain;
        $this->getAll();
        $this->sort();
        // This will be false if no records were found.
        return $this->dns_all;
    }

    public function domain()
    {
        return $this->domain;
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
    public function get($type)
    {
        return dns_get_record($this->domain, $type);
    }

    /**
     * Get all dns records and store them
     *
     * It might be faster in the future to find
     * the records in dns_all instead of using multiple
     * calls to dns_get_record.
     */
    public function getAll()
    {
        // Check to see if this has already been done.
        if (is_array($this->dns_all) && count($this->dns_all) == 0) {
            $this->dns_all = $this->get(DNS_ALL);
        }
        if (count($this->dns_all) == 0) {
            $this->dns_all = false;
        }
        return $this->dns_all;
    }

    /**
     * separate the records out by type
     */
    public function sort()
    {
        if (is_array($this->dns_all)) {
            foreach ($this->dns_all as $dns_record) {
                // just to clean up the code a little
                $current_type = $dns_record['type'];
                if (!isset($this->dns_sorted[$current_type])) {
                    $this->dns_sorted[$current_type] = array();
                }
                $this->dns_sorted[$current_type][] = $dns_record;
            }
        } else {
            return false;
        }
    }

    /**
     * return all dns records unsorted
     */
    public function raw()
    {
        return $this->dns_all;
    }

    public function sorted()
    {
        return $this->dns_sorted;
    }

    /**
     * Return just the nameserver(NS) records
     *    
     * @return array of name server records.    
     */
    public function nameservers()
    {
        return $this->dns_sorted['NS'];
    }

    /**
     *
     * If there are no records, then return false.
     * If there are records, but no NS records, return false
     *
     */
    public function check()
    {
        if ($this->dns_all === false) {
            return false;
        } elseif (!isset($this->dns_sorted['ns'])) {
            // We should probably have a nameserver set
            return false;
        } else {
            return true;
        }

    }
}

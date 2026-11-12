<?php

namespace App\Support;

class UbiquitiNetworkingCatalog
{
    /**
     * Flat category hierarchy. Each entry:
     * [name, slug, parent_slug, seo_title|null, h1|null, focus_keyword|null, meta_description|null]
     *
     * @return array<int, array{0: string, 1: string, 2: string|null, 3: string|null, 4: string|null, 5: string|null, 6: string|null}>
     */
    public static function categories(): array
    {
        return [
            ['Ubiquiti Networking', 'ubiquiti-networking', null,
                'Ubiquiti Networking Products Kenya | Ubiquiti Kenya',
                'Ubiquiti Networking Products in Kenya',
                'Ubiquiti Networking Products Kenya',
                'Ubiquiti networking equipment including UniFi access points, switches, gateways, wireless ISP equipment, airMAX, airFiber and networking accessories.',
            ],

            // Access Points
            ['Ubiquiti Access Points', 'ubiquiti-access-points', 'ubiquiti-networking',
                'Ubiquiti Access Points Kenya | UniFi AP Prices',
                'Ubiquiti Access Points in Kenya',
                'Ubiquiti Access Points Kenya',
                'Shop Ubiquiti access points in Kenya for homes, offices, hotels, campuses and managed WiFi networks.',
            ],
            ['WiFi 7 Access Points', 'wifi-7-access-points', 'ubiquiti-access-points', null, null, null, null],
            ['WiFi 6 Access Points', 'wifi-6-access-points', 'ubiquiti-access-points', null, null, null, null],
            ['Indoor Access Points', 'indoor-access-points', 'ubiquiti-access-points', null, null, null, null],
            ['Outdoor Access Points', 'outdoor-access-points', 'ubiquiti-access-points', null, null, null, null],
            ['Long Range Access Points', 'long-range-access-points', 'ubiquiti-access-points', null, null, null, null],
            ['Wall Access Points', 'wall-access-points', 'ubiquiti-access-points', null, null, null, null],
            ['Enterprise Access Points', 'enterprise-access-points', 'ubiquiti-access-points', null, null, null, null],

            // Switches
            ['Ubiquiti UniFi Switches', 'ubiquiti-switches', 'ubiquiti-networking',
                'Ubiquiti Switches Kenya | UniFi Switch Prices',
                'Ubiquiti Switches in Kenya',
                'Ubiquiti Switches Kenya',
                'Shop Ubiquiti UniFi switches in Kenya including PoE, non-PoE, 8-port, 16-port, 24-port, 48-port, multi-gigabit and 10G switches.',
            ],
            ['PoE Switches', 'poe-switches', 'ubiquiti-switches', null, null, null, null],
            ['Non-PoE Switches', 'non-poe-switches', 'ubiquiti-switches', null, null, null, null],
            ['8 Port Switches', '8-port-switches', 'ubiquiti-switches', null, null, null, null],
            ['16 Port Switches', '16-port-switches', 'ubiquiti-switches', null, null, null, null],
            ['24 Port Switches', '24-port-switches', 'ubiquiti-switches', null, null, null, null],
            ['48 Port Switches', '48-port-switches', 'ubiquiti-switches', null, null, null, null],
            ['Multi-Gigabit Switches', 'multi-gigabit-switches', 'ubiquiti-switches', null, null, null, null],
            ['10G Switches', '10g-switches', 'ubiquiti-switches', null, null, null, null],
            ['Enterprise Switches', 'enterprise-switches', 'ubiquiti-switches', null, null, null, null],
            ['Aggregation Switches', 'aggregation-switches', 'ubiquiti-switches', null, null, null, null],

            // Cloud Gateways
            ['Ubiquiti Cloud Gateways', 'ubiquiti-cloud-gateways', 'ubiquiti-networking',
                'Ubiquiti Cloud Gateways Kenya | UniFi Gateway Prices',
                'Ubiquiti Cloud Gateways in Kenya',
                'Ubiquiti Cloud Gateway Kenya',
                'Compare Ubiquiti Cloud Gateway devices in Kenya for UniFi routing, security, VPN, application hosting and network management.',
            ],

            // Routers
            ['Ubiquiti Routers', 'ubiquiti-routers', 'ubiquiti-networking',
                'Ubiquiti Routers Kenya | UniFi Router Prices',
                'Ubiquiti Routers in Kenya',
                'Ubiquiti Routers Kenya',
                'Shop Ubiquiti routers in Kenya for UniFi networks, ISP deployments, VPN, multi-WAN and business internet gateways.',
            ],
            ['UniFi Routers', 'unifi-routers', 'ubiquiti-routers', null, null, null, null],
            ['EdgeRouters', 'edgerouters', 'ubiquiti-routers', null, null, null, null],
            ['VPN Routers', 'vpn-routers', 'ubiquiti-routers', null, null, null, null],
            ['Multi-WAN Routers', 'multi-wan-routers', 'ubiquiti-routers', null, null, null, null],

            // airMAX
            ['Ubiquiti airMAX', 'ubiquiti-airmax', 'ubiquiti-networking',
                'Ubiquiti airMAX Kenya | Wireless ISP Equipment',
                'Ubiquiti airMAX Products in Kenya',
                'Ubiquiti airMAX Kenya',
                'Shop Ubiquiti airMAX equipment in Kenya including LiteBeam, NanoBeam, NanoStation, PowerBeam, Rocket and antennas.',
            ],
            ['LiteBeam', 'litebeam', 'ubiquiti-airmax', null, null, null, null],
            ['NanoBeam', 'nanobeam', 'ubiquiti-airmax', null, null, null, null],
            ['NanoStation', 'nanostation', 'ubiquiti-airmax', null, null, null, null],
            ['PowerBeam', 'powerbeam', 'ubiquiti-airmax', null, null, null, null],
            ['Rocket', 'rocket', 'ubiquiti-airmax', null, null, null, null],
            ['Rocket Prism', 'rocket-prism', 'ubiquiti-airmax', null, null, null, null],
            ['airMAX Antennas', 'airmax-antennas', 'ubiquiti-airmax', null, null, null, null],

            // Point-to-Point
            ['Ubiquiti Point-to-Point Wireless', 'ubiquiti-point-to-point', 'ubiquiti-networking',
                'Ubiquiti Point to Point Kenya | Wireless Bridge Equipment',
                'Ubiquiti Point-to-Point Wireless Kenya',
                'Ubiquiti Point to Point Kenya',
                'Compare Ubiquiti point-to-point wireless equipment in Kenya for building links, ISP backhaul and remote connectivity.',
            ],

            // airFiber
            ['Ubiquiti airFiber', 'ubiquiti-airfiber', 'ubiquiti-networking',
                'Ubiquiti airFiber Kenya | Wireless Backhaul Equipment',
                'Ubiquiti airFiber Products in Kenya',
                'Ubiquiti airFiber Kenya',
                'Shop Ubiquiti airFiber equipment in Kenya for high-capacity wireless backhaul and ISP links.',
            ],

            // UISP
            ['Ubiquiti UISP', 'ubiquiti-uisp', 'ubiquiti-networking',
                'Ubiquiti UISP Kenya | ISP Networking Equipment',
                'Ubiquiti UISP Products in Kenya',
                'Ubiquiti UISP Kenya',
                'Browse Ubiquiti UISP routers, switches, wireless, fiber and power products in Kenya for ISP networks.',
            ],
            ['UISP Routers', 'uisp-routers', 'ubiquiti-uisp', null, null, null, null],
            ['UISP Switches', 'uisp-switches', 'ubiquiti-uisp', null, null, null, null],
            ['UISP Wireless', 'uisp-wireless', 'ubiquiti-uisp', null, null, null, null],
            ['UISP Fiber', 'uisp-fiber', 'ubiquiti-uisp', null, null, null, null],
            ['UISP Power', 'uisp-power', 'ubiquiti-uisp', null, null, null, null],

            // Antennas
            ['Ubiquiti Antennas', 'ubiquiti-antennas', 'ubiquiti-networking',
                'Ubiquiti Antennas Kenya | Sector, Omni & Dish Antennas',
                'Ubiquiti Antennas in Kenya',
                'Ubiquiti Antennas Kenya',
                'Shop Ubiquiti antennas in Kenya including sector, omni, dish, airMAX and airFiber antenna options.',
            ],
            ['Sector Antennas', 'sector-antennas', 'ubiquiti-antennas', null, null, null, null],
            ['Omni Antennas', 'omni-antennas', 'ubiquiti-antennas', null, null, null, null],
            ['Dish Antennas', 'dish-antennas', 'ubiquiti-antennas', null, null, null, null],
            ['airFiber Antennas', 'airfiber-antennas', 'ubiquiti-antennas', null, null, null, null],

            // Network Accessories
            ['Ubiquiti Network Accessories', 'ubiquiti-network-accessories', 'ubiquiti-networking',
                'Ubiquiti Network Accessories Kenya | PoE, SFP & Fiber',
                'Ubiquiti Network Accessories in Kenya',
                'Ubiquiti Network Accessories Kenya',
                'Shop Ubiquiti network accessories in Kenya including PoE injectors, SFP modules, DAC cables, surge protectors, mounts and power supplies.',
            ],
            ['PoE Injectors', 'poe-injectors', 'ubiquiti-network-accessories', null, null, null, null],
            ['SFP Modules', 'sfp-modules', 'ubiquiti-network-accessories', null, null, null, null],
            ['SFP+ Modules', 'sfp-plus-modules', 'ubiquiti-network-accessories', null, null, null, null],
            ['DAC Cables', 'dac-cables', 'ubiquiti-network-accessories', null, null, null, null],
            ['Fiber Accessories', 'fiber-accessories', 'ubiquiti-network-accessories', null, null, null, null],
            ['Ethernet Surge Protectors', 'ethernet-surge-protectors', 'ubiquiti-network-accessories', null, null, null, null],
            ['Network Mounts', 'network-mounts', 'ubiquiti-network-accessories', null, null, null, null],
            ['Power Supplies', 'power-supplies', 'ubiquiti-network-accessories', null, null, null, null],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function products(): array
    {
        $path = database_path('seeders/data/ubiquiti_products.php');

        return file_exists($path) ? require $path : [];
    }

    /**
     * Kenyan retail prices (KES) sourced from ctcsolutions.co.ke, keyed by
     * product slug. Only products with a confirmed match are listed; the
     * importer leaves price NULL for everything else.
     *
     * @return array<string, float>
     */
    public static function kenyanPrices(): array
    {
        return [
            'ubiquiti-u7-lite' => 17500.00,
            'ubiquiti-u7-long-range' => 25000.00,
            'ubiquiti-u7-pro' => 28500.00,
            'ubiquiti-u7-pro-max' => 42000.00,
            'ubiquiti-u7-pro-wall' => 33000.00,
            'ubiquiti-u7-in-wall' => 22000.00,
            'ubiquiti-u7-outdoor' => 38000.00,
            'ubiquiti-u7-pro-outdoor' => 44500.00,
            'ubiquiti-u6-plus' => 17000.00,
            'ubiquiti-u6-pro' => 24000.00,
            'ubiquiti-u6-enterprise' => 40000.00,
            'ubiquiti-u6-in-wall' => 24000.00,
            'ubiquiti-u6-mesh' => 25000.00,
            'unifi-lite-16-poe' => 31000.00,
            'unifi-standard-24-poe' => 65000.00,
            'unifi-standard-48-poe' => 90000.00,
            'ubiquiti-dream-machine-pro-max' => 87000.00,
            'ubiquiti-airmax-nanostation-m5' => 11500.00,
            'ubiquiti-airmax-nanostation-m2' => 10500.00,
            'ubiquiti-airmax-nanostation-loco-m5' => 8800.00,
            'ubiquiti-airmax-nanostation-loco-m2' => 7000.00,
            'ubiquiti-airmax-nanostation-5ac-loco' => 6300.00,
            'ubiquiti-airmax-nanostation-5ac' => 15000.00,
            'ubiquiti-airmax-litebeam-m5' => 6500.00,
            'ubiquiti-airmax-liteap-120' => 11500.00,
            'ubiquiti-airmax-liteap-gps' => 13000.00,
            'ubiquiti-airmax-powerbeam-m2-400' => 12000.00,
            'ubiquiti-airmax-powerbeam-m5-400' => 12000.00,
            'ubiquiti-airmax-powerbeam-5ac-gen2' => 17000.00,
            'ubiquiti-airmax-powerbeam-5ac-500' => 21000.00,
            'ubiquiti-airmax-powerbeam-5ac-620' => 32000.00,
            'ubiquiti-airmax-rocket-5ac-lite' => 17000.00,
            'ubiquiti-airmax-rocket-prism-5ac-gen2' => 31000.00,
            'ubiquiti-airmax-sector-antenna-am-5g19-120' => 25000.00,
            'ubiquiti-airmax-rocketdish-5g30' => 22500.00,
            'ubiquiti-airfiber-5xhd' => 50000.00,
            'ubiquiti-poe-24-24v-05a' => 2000.00,
            'ubiquiti-poe-48-48v-05a' => 2500.00,
        ];
    }
}

<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/Cargo.php';
require_once __DIR__ . '/Port.php';
require_once __DIR__ . '/Ship.php';
require_once __DIR__ . '/Shipment.php';

/** Menghubungkan route kapal dengan model, entity, dan view yang sesuai. */
class ShipController extends BaseController {
    public function __construct() { $this->model = new Ship(); }
    protected function slug(): string { return 'ships'; }
    protected function view(): string { return 'ships'; }
    protected function newEntity(): BaseEntity { return new ShipEntity(); }
    protected function entityFromInput(array $input): BaseEntity { return ShipEntity::fromArray($input); }
}

/** Menghubungkan route barang dengan model, entity, dan view yang sesuai. */
class CargoController extends BaseController {
    public function __construct() { $this->model = new Cargo(); }
    protected function slug(): string { return 'cargos'; }
    protected function view(): string { return 'cargos'; }
    protected function newEntity(): BaseEntity { return new CargoEntity(); }
    protected function entityFromInput(array $input): BaseEntity { return CargoEntity::fromArray($input); }
}

/** Menghubungkan route pelabuhan dengan model, entity, dan view yang sesuai. */
class PortController extends BaseController {
    public function __construct() { $this->model = new Port(); }
    protected function slug(): string { return 'ports'; }
    protected function view(): string { return 'ports'; }
    protected function newEntity(): BaseEntity { return new PortEntity(); }
    protected function entityFromInput(array $input): BaseEntity { return PortEntity::fromArray($input); }
}

/** Menangani transaksi dan menyiapkan pilihan dropdown untuk form-nya. */
class ShipmentController extends BaseController {
    private Shipment $shipmentModel;

    public function __construct() {
        $this->shipmentModel = new Shipment();
        $this->model = $this->shipmentModel;
    }

    protected function slug(): string { return 'shipments'; }
    protected function view(): string { return 'shipments'; }

    protected function newEntity(): BaseEntity {
        return (new ShipmentEntity())->setType(Shipment::TYPE_EXPORT)->setStatus(Shipment::STATUS_PENDING);
    }

    protected function entityFromInput(array $input): BaseEntity {
        return ShipmentEntity::fromArray($input);
    }

    /** Label pilihan dibuat di controller agar view hanya bertugas menampilkan. */
    protected function extraViewData(): array {
        $ships = $cargos = $ports = [];
        foreach ((new Ship())->getOptions() as $ship) {
            $ships[$ship->getId()] = $ship->getName() . ' (IMO ' . $ship->getImoNumber() . ', ' . $ship->getCapacityTeu() . ' TEU)';
        }
        foreach ((new Cargo())->getOptions() as $cargo) {
            $cargos[$cargo->getId()] = $cargo->getName() . ' (HS ' . $cargo->getHsCode() . ')';
        }
        foreach ((new Port())->getOptions() as $port) {
            $ports[$port->getId()] = $port->getLabel();
        }
        $types = $this->shipmentModel->enumValues('type');
        $statuses = $this->shipmentModel->enumValues('status');

        return [
            'shipOptions' => $ships,
            'cargoOptions' => $cargos,
            'portOptions' => $ports,
            'typeOptions' => array_combine($types, $types),
            'statusOptions' => array_combine($statuses, $statuses),
        ];
    }
}
<?php declare(strict_types=1);
namespace Modules\POS\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\POS\Domain\Entities\POSSession;
use Modules\POS\Domain\RepositoryInterfaces\POSSessionRepositoryInterface;
use Modules\POS\Infrastructure\Persistence\Eloquent\Models\POSSessionModel;
class EloquentPOSSessionRepository implements POSSessionRepositoryInterface {
    public function __construct(private readonly POSSessionModel $model) {}
    public function findById(int $id): ?POSSession { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findOpenByTerminal(int $terminalId): ?POSSession { $m=$this->model->newQuery()->where('terminal_id',$terminalId)->where('status','open')->first(); return $m?$this->toEntity($m):null; }
    public function save(POSSession $s): POSSession {
        $m=$s->getId()?$this->model->newQuery()->findOrFail($s->getId()):new POSSessionModel();
        $m->tenant_id=$s->getTenantId();$m->terminal_id=$s->getTerminalId();$m->user_id=$s->getUserId();$m->opening_cash=$s->getOpeningCash();$m->closing_cash=$s->getClosingCash();$m->status=$s->getStatus();$m->opened_at=$s->getOpenedAt()->format('Y-m-d H:i:s');$m->closed_at=$s->getClosedAt()?->format('Y-m-d H:i:s');
        $m->save(); return $this->toEntity($m);
    }
    private function toEntity(POSSessionModel $m): POSSession { return new POSSession($m->id,$m->tenant_id,$m->terminal_id,$m->user_id,(float)$m->opening_cash,$m->closing_cash!==null?(float)$m->closing_cash:null,$m->status,new \DateTimeImmutable($m->opened_at->toDateTimeString()),$m->closed_at?new \DateTimeImmutable($m->closed_at->toDateTimeString()):null); }
}

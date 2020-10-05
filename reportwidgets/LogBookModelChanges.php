<?php

namespace Jacob\Logbook\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Jacob\Logbook\Models\Log;

class LogBookModelChanges extends ReportWidgetBase
{
    /** @var LengthAwarePaginator */
    public $logs;

    /** @var bool */
    public $showUndoChangesButton;

    public function defineProperties(): array
    {
        return [
            'limitPerPage' => [
                'title' => 'Amount of logs per page',
                'default' => 20,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
            ],
        ];
    }

    public function render(): string
    {
        return $this->makePartial('default', [
            'logbook' => $this->getLogBookPartial()
        ]);
    }

    public function onLogBookChangePage(): array
    {
        $page = (int) post('page', 1);

        return [
            '#jacob-logbook-report-widget' => $this->getLogBookPartial($page),
        ];
    }

    private function getLogBookPartial($page = 1): string
    {
        $this->logs = Log::query()
            ->orderBy('updated_at', 'desc')
            ->paginate($this->property('limitPerPage', 20), $page);

        $this->showUndoChangesButton = false;

        return $this->makePartial('$/jacob/logbook/formwidgets/logbook/partials/_logbook.htm');
    }
}

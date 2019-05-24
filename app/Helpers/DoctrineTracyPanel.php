<?php

namespace App\Helpers;

use Tracy\IBarPanel;

class DoctrineTracyPanel implements IBarPanel, \Doctrine\DBAL\Logging\SQLLogger
{
	/** @var array */
	private $queries = [];

	private $icon;

	public function getTab()
	{
		$this->icon = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACUAAAAyCAYAAADbTRIgAAAAAXNSR0IArs4c'.
			'6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAadEVYdFNvZnR3YXJlAFBhaW50Lk5FVCB2My41LjEwMPRyoQ'.
			'AABONJREFUWEe9WFtsVEUYrtEQE4zRqA8mBqJGHlTWiBdAUXxAH9QEL2gQFJSrGoFtK4Ix3q8J1kIVCREEEjCaqNEY0QcevEIDRKi0'.
			'SQm2AhZdlaqlu3vO7O454/ef/q175sycy7qHL/nS7cz8//ftnNl/5kxDFIpLM2dZSzLPgj34XMbff8AP8flyHnJyAeELYOAwKDW0wJ'.
			'k89OQBopurTOhYAe/m4enDahxPpv6oMmCiDU7jsHSRz14xCmJulXgYB4tLMhM5ND0Uh2bqT0U8jP0wlv7ih9B6RTiKx2DsQg5PB/j1'.
			'nQ+hnCIcxUMUxynSAb75VRAaUISj2AFjZ3OKdABjN0GI6pLOgInfw9hoTpEOIDIdpIquM2DilzA2ilOkA4jMBeOWiWF+UFyWOZVTpA'.
			'OIZBXROFyfz44/hVPUH/mmy8jYi4poHL6KgsxZ6ogCCirWyGQIbFcE43I5p6oPYOYSJP1UEUlKWo8LOGXtyC/LnIaS8BSSJS0JJtLJ'.
			'YganTw7Mzlgk2FWVsF6kk8UtLBMfMDQVgeEbcuPVUqy6T9ovTdf3h7MATmG5aOBx3YUA+ja6ZB5LG5uke6JfDsM5fEDar9ypHRtCOl'.
			'mMY1kzMEN3YHBJCfbRfv42KSsltlOF4gkpXp+tjQlhFzTPYPkg0DkJg4pKUIClbc+wCw2swVqMvcMW/IChc9DZpwzWUrTOYQcGFGGs'.
			'5QFtrIFUKoJHajRuqRoUSad7FzswwMpL8UYiY52+PRKzdCUaE2209pNTpdt3kB0YQMYwq7p4A2exJW+WtiqdsWivvEE6R7vYgQE2jK'.
			'1+UBuvYTttZQ2FpZkz8U/k4jbRfuJ66fz8IzswwC7ENoanNo5q0gxdZyIuv046PT+wAwPiG2uiR9emNI7QfvpmKdYuluLtR6R4a6EU'.
			'bfPBeV5y0TrXW8iiZTZKwCwp1syT7vE+dmAA6pj9cmSB/YhMfaU0Sis7QVb2bpfSdTlb/eDmeqXVdI1fz89uMhW4wCh/vIpTpIPyJ6'.
			'0+PYV5MjWoNErnYDuHpwO3r9unp9AlU4E3FKd3P4enAzfX49NTKMiUUBpl+fO1HJ4Oyp+1+fQU5shU8LW8+VpZ2b8jnYXef0xaj0/y'.
			'6/m5m0x9pzSO0F55o/cTFi33S/HmgqHSsO5RLhGLUB7QtuYhbyuh/92/fmVpA8oCZSRy29lEplYrjclJxbN3HysbUC7J0oZGfXwVUc'.
			'wXkalb1Y5E9LaZDlY2QBQxuw/r4/10sc2MoRMC3dj9rnTGor1iinSOhG/IbmEgziMb5jdyxUUjp4TnlM5o4qUhar9zB45L+7V79PEa'.
			'4tHdO+QIwGzRXXmcS9cRlndsZmk96Fdmv3C7NtbAAwX1IgSNc5RBRtIBT/viwHB/6/E2c12sgbSWgsfhQta7eH1fGawlvRiY4Bzp9E'.
			'qJLi6EG9hGEHA7GgP2KgFBori6f+fYxn9wDu1BeZisjzGzAwfN8Bs/GDsPAzuVwABpAdOseEANqnz9XtSRRMej0BvD0uHAwHMREOsO'.
			'gcqC1TxR2xfBn6BzMUvGA6b0dASuA5NeJ8bhF/TFWSo5UDumIUmXkrRW5pBv/mA28/+vGql+INlMJP0WrGXm9oGPRS7oWkELEwILwX'.
			'fBdpBe9elCjS7D6O8v4E5wI7iY1o3dfClHx0FDw7+Sb2560wLhYgAAAABJRU5ErkJggg==" style="height: 15px" />';

		$time = number_format(array_reduce($this->queries, function($prev, $value) {
			return $value[2] + $prev;
		}, 0), 8);

		return '
        <span class="tracy-label" title="Doctrine DBAL Panel">
            ' . $this->icon . ' (' . count($this->queries) . ' / ' . round($time, 2) . ' ms)
        </span>';
	}

	public function getPanel()
	{
		return '
        <h1>'.$this->icon.' &nbsp; Doctrine DBAL</h1>
        <div class="tracy-inner doctrinePanel">
            <p>
                <table width="100%">' . $this->getHtml() . '</table>
            </p>
        </div>';
	}

	protected function getHtml()
	{
		static $html = null;
		if ($html === null)
		{
			$html = '<thead><tr><th>SQL</th><th>Time (ms)</th><th>Params</th></tr></thead>';
			$baseRow = '<tr><td>%s</td><td>%s</td><td><pre>%s</pre></td></tr>';
			foreach ($this->queries as $data)
			{
				[$sql, $params, $time] = $data;
				$html .= sprintf($baseRow, \Nette\Database\Helpers::dumpSql($sql), number_format($time, 3), implode(', ', $params ?: []));
			}
		}

		return $html;
	}

	public function startQuery($sql, array $params = null, array $types = null)
	{
		$this->queries[] = [$sql, $params, microtime(true)];
	}

	public function stopQuery()
	{
		$time = &$this->queries[count($this->queries) - 1][2];
		$time = (microtime(true) - $time) * 1000;
	}
}

# Compact Key-Value List #
## (Compact\_KeyValue\_List) ##

### Zusammenfassung ###
Ziel dieser PHP Klasse ist es, auf einfache Art und Weise eine Liste von Schlüssel-Werte-Paaren zu erstellen. Dabei hat jeder Schlüssel und jeder Wert sein eigenes Input-Feld. Zusätzlich enthält die Liste noch zwei Buttons um neue Einträge hinzuzufügen bzw. bestehende zu löschen.
Die Klasse ist darauf ausgelegt mit der WordPress Settings API zusammen zu arbeiten. Die Input-Felder haben deswegen einen `name` Attribut im Format `name="options-name[left|right][]"`. Dadurch wird beim Absenden im POST-Header ein Array im Format

	'options-name' => array(
		'left'  => array( [elemnte] ),
		'right' => array( [elemente] )
	)
übergeben. Diese beiden Einzel-Arrays entsprechen jeweils den Werten in der linken Spalte (`left`) bzw. rechten Spalte (`right`). Dies macht es möglich mittels `array_combine( [array left], [array right] )` die Schlüssel-Werte-Paarungen wieder zu einen einheitlichen Array zusammen zu führen.

Da die Liste ein eigenes Stylesheet und spezielles JavaScript benötigt, ist die Klasse in der Lage beides automatisch, basierend auf einer vorgegebenen CSS-Klasse, zu erstellen.


### Initialisierung ###
Zur Generierung einer Liste benötigt die Klasse sowohl die Elemente die angezeigt werden sollen, als auch eine Basis-Konfiguration.

Beispiel:

		$elements = get_option( $option_name );

		$config = array(
		 	'option_name' => $option_name,
			'buttons'     => array(
					'add' => __( 'Add Item', 'textdomain' ),
					'del' => array( __( 'Del Item', 'textdomain' ), 'delete' )
			)
		);

		$list = new Compact_KeyValue_List( $elements, $config );

		echo $list->get_list();

Eine Vorlage für die erwarteten Konfigurationsparameter kann über die Methode `Compact_KeyValue_List::get_default_config()` abgerufen werden.

Wird die Klasse ohne Angabe von Parametern initialisiert, so ist es ebenso möglich die Konfiguration der Klassen-Eigenschaften direkt vorzunehmen.

Beispiel Initialisierung ohne Parameter:

		$list = new Compact_KeyValue_List();

		$list->elements     = get_option( $option_name );
		$list->options_name = $options_name;
		$list->buttons      = array(
			'add' => __( 'Add Item', 'textdomain' ),
			'del' => array( __( 'Del Item', 'textdomain' ), 'delete' )
		);

		echo $list->get_list();

 

##### Options Name #####
Prinzipiell wird nur der Name für die verwendete Option in der Datenbank und die Konfiguration der Buttons übergeben. Der Options-Name kann auch von dem in der Datenbank verwendeten Options-Namen abweichen, dabei ist jedoch zu beachten das der als Options-Name übergebene Parameter beim Absenden der Daten im POST-Header verwendet wird. Dies ist vor allem bei der Verwendung der Settings API zu beachten, da hier eine Validierungs-Funktion verwendet werden kann die nur dann greift, wenn der Options-Name richtig gesetzt ist.

Beispiel für eine Validierungsfunktion bei der Verwendung der Settings API:

	function display_list() {
		
		// es wird angenommen das der Options-Name und die Validierungs-Funktion
		// bereits mit register_settings() registriert wurde

		$option_name = 'foobar';

		$elements = get_option( $option_name );

		$config = array(
		 	'option_name' => $option_name,
			'buttons'     => array(
					'add' => __( 'Add Item', 'textdomain' ),
					'del' => array( __( 'Del Item', 'textdomain' ), 'delete' )
			)
		);

		$list = new Compact_KeyValue_List( $elements, $config );

		echo $list->get_list();
	}

	function validate_options( $input ) {

		if ( isset( $input['left'] ) && isset( $input['right'] ) )
			$input = array_combine( $input['left'] , $input['right'] );

		return $input;
	
	}

In diesem Beispiel würde das Array `$input` lediglich aus den Arrays `left` und `right` bestehen. Im POST-Header wäre das Array `foobar` vorhanden welches wiederrum die Arrays `left` und `right` enthält. Somit ist es möglich sich manuell in der Speicherung von Optionen einzuhängen.

Beispiel ohne Verwendung der Settings API:

	$postheader_value = 'foobar';
	add_action( 'sanitize_option_' . {$postheader_value}, 'custom_sanitizing_options', 10, 0 ); 

	function custom_sanitizing_options() {

		$postheader_value = 'foobar';

		// wenn der gesuchte POST-Header nicht vorhanden ist, abbrechen
		if ( ! key_exists( $postheader_value, $_POST ) )
			return false;

		$key_values  = $_POST[ $postheader_value ];
		$option_name = 'bazbazbaz';

		$_POST[ $option_name ] = array_combine( $key_values['left'], $key_values['right'] );
	
		// da der POST-Header direkt bearbeitet wird, ist es nicht nötig das Array zurück zu geben.	
		return true;
	}

##### Buttons (Schaltflächen) #####
Die Buttons (Schaltflächen) werden in Form eines Arrays definiert. Dieses Array muss die Schlüssel `add` und `del` für die Buttons zum Hinzufügen bzw. Löschen von Zeilen enthalten.

In der einfachsten Form wird lediglich der Text für die Buttons übergeben. Soll zusätzlich auch noch das Aussehen der Buttons definiert werden, so muss der Button mittels eines Arrays konfiguriert werden. Dieses Array kann als einfaches oder assoziatives Array angegeben werden.

Beispiel:

		$config = array(
		 	'option_name' => $option_name,
			'buttons'     => array(
					'add' => array( 'text' => __( 'Add Item', 'textdomain' ) 'type' => 'primary' ),
					'del' => array( __( 'Del Item', 'textdomain' ), 'delete' )
			)
		);

Die möglichen Typen für die Buttons sind `primary` (Standard), `secondary` und `delete`. Siehe hierzu auch den [Abschnitt zu get\\_submit_button() im WordPress Codex](http://codex.wordpress.org/Function_Reference/get_submit_button).


##### Filter #####
Die Klasse verwendet verschiedene Filter um die Ausgabe der Liste zu beeinflussen. Alle Filter beginnen mit `compact_keyvalue_list-`. Die Filter im Einzelnen:

- `compact_keyvalue_list-inner_list` filtert den inneren Teil der Liste. Dies sind alle Zeilen, wobei eine Zeile  aus einem `div`-Container besteht der jeweils zwei `input` Felder enthält.
- `compact_keyvalue_list-outer_list` filtert den äusseren `div`-Container der die inner Liste enthält.
- `compact_keyvalue_list-list_buttons` filtert den Bereich mit den Buttons zum Hinzufügen udn Löschen von Zeilen. Dieser Bereich besteht im wesentlichen aus einen `div`-Container mit zwei `p`-Tags die wiedrum jeweils die Buttons enthalten.
- `compact_keyvalue_list-complete_list` filtert die komplette HTML-Ausgabe der Liste.

### Automatisches Hinzufügen von JavaScript und Stylesheet ###
Da die erzeugte Liste stark von einem speziellen Stylesheet abhängt und nur in Verbindung mit einem speziellen JavaScript (jQuery) funktioniert, ist die Klasse im Stande beides automatisch zu erzeugen.

##### Erzeugung während des Scriptablaufes #####
Obwohl nicht zu empfehlen, ist die Klasse in der Lage sowohl JavaScript als auch Stylesheet dynamisch während des Scriptablaufes zu erzeugen und auszugeben. Zudem kann die Klasse auch automatisch den entsprechenden Hook setzen damit JavaScript und Stylesheet auf der gewünschten Seite eingebunden werden. Dieses Vorgehen ist für Rapid Prototyping und während der Entwickelung / Testphase sinnvoll, sollte jedoch nicht im produktiven Einsatz verwendet werden.

Beispiel:

	add_action(
		'init',
		'plugin_init',
		10,
		0
	);
	
	function plugin_init() {
	
		if ( ! is_admin() )
			return false;
	
		require_once 'autoload.php';
	
		new Autoload( dirname( __FILE__ ) );
	
		/*
		 * This class can automatically create the JS and stylesheet for the list on the fly
		 * It expect three params:
		 *  - the slug to enqueue JS and CSS
		 *  - the css-class to be used inside the list (and the JS depends on)
		 *  - the pageslug where the list should be displayed
		 *
		 *  This static call is needed to enqueue the JS and CSS
		 */
		$slug      = 'compact-list';
		$css_class = 'compactlist';
		$pageslug  = 'options-writing.php';

		Compact_KeyValue_List::enqueue_scripts( $slug, $css_class, $pageslug );
	
		$menuitem = new MenuItem();
	
	}

In diesem Beispiel wird während der Ausführung des Hooks `plugins_init` sowohl das Einfügen (enqueueing) als auch die Ausgabe des JavaScripts und des Stylesheets initialisiert. 

In diesem Beispiel wird unter den Menüpunkt "Einstellungen -> Schreiben" durch die Klasse `MenuItem` ein weiteres Feld eingefügt in dem die Liste dargestellt wird. Die Klasse `Compact_KeyValue_List` fügt den Hook `load-{$pageslug}` hinzu, so dass beim Aufruf des Menüpunktes "Schreiben" das JavaScript und das Stylesheet unter der Verwendung des Slugs `compact-list` eingefügt werden.

Die Angabe der CSS-Klasse (`$css_Class`) ist zwingend nötig und ermöglicht es mehrere Listen auf der gleichen Seite zu verwenden. Allerdings würde dann bei Verwendung dieser Methode für jede Liste ein eigenes JavaScript und ein eigenes Stylesheet erzeugt werden, weshalb von der Verwendung dieser Methode im produktiven Einsatz abzuraten ist.

##### Erzeugung von statischen Scripten während der Pluginaktivuierung #####
Sinnvoller ist das Erzeugen von statischen Dateien während er Pluginaktivierung. Hierzu stellt die Klasse statische Methoden bereit zur Erstellung des JavaScripts und des Stylesheets welche dann als Datei gespeichert werden können.

	register_activation_hook( __FILE__, 'plugin_activate' );

	function plugin_activate() {
		
		require_once 'compact_keyyvalue_list.php';

		$css_class = 'compactlist';

		$js  = Compact_KeyValue_List::get_js( $css_class );
		$css = Compact_KeyValue_List::get_stylesheet( $css_class );

		/*
		 * code to save content of $js and $css
		 */

	}

Da es nicht immer sichergestellt ist das Dateien in den jeweils gewünschten Verzeichnissen angelegt werden können, kann die zuerst genannte Methode als Fallback genutzt werden sofern das erstellen der Dateien fehl schlägt.

##### Kombination mehrerer Listen in statischen Dateien #####
Derzeit ist noch keine Methode implementiert die es ermöglicht mehrere CSS-Klassen anzugeben so das ein JavaScript bzw. Stylesheet erzeugt wird das gleichzeitig für mehrere Listen benutzt werden kann. Werden mehrere Listen verwendet und sollen statische JavaScript- und Stylesheet-Dateien verwendet/erzeugt werden, so müssen diese Dateien manuell bzw. scriptgesteuert erzeugt werden.

Die manuelle erzeugung bzw. Nachbearbeitung ist auch in Hinsicht auf Anpassung des Stylesheets zu bevorzugen.

#### Anmerkung ####
Die Klasse berücksichtigt die WordPress-Konstante `SCRIPT_DEBUG` und gibt dementsprechend komprimiertes bzw. unkomprimiertes JavaScript / Stylesheet aus.   
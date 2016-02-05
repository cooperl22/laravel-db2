<?php
namespace Cooperl\Database\DB2\Connectors;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class ODBCConnector extends Connector implements ConnectorInterface
{

    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        $options = $this->getOptions($config);

        $connection = $this->createConnection($dsn, $config, $options);

        if (isset($config['schema']))
        {
            $schema = $config['schema'];

          $connection->prepare("set schema $schema")->execute();
        }

        return $connection;
    }

    protected function getDsn(array $config) {
        extract($config);

        $dsn = "odbc:"
             // General settings
             . "DRIVER={$ibmDriver};"
             . "SYSTEM=$host;"
             . "UserID=$username;"
             . "Password=$password;"
             //Server settings
             . "DATABASE=$database;"
             . "SIGNON=$signon;"
             . "SSL=$ssl;"
             . "CommitMode=$commitMode;"
             . "ConnectionType=$connectionType;"
             . "DefaultLibraries=$defaultLibraries;"
             . "Naming=$naming;"
             . "UNICODESQL=$unicodeSql;"
             // Format settings
             . "DateFormat=$dateFormat;"
             . "DateSeperator=$dateSeperator;"
             . "Decimal=$decimal;"
             . "TimeFormat=$timeFormat;"
             . "TimeSeparator=$timeSeparator;"
             // Performances settings
             . "BLOCKFETCH=$blockFetch;"
             . "BlockSizeKB=$blockSizeKB;"
             . "AllowDataCompression=$allowDataCompression;"
             . "CONCURRENCY=$concurrency;"
             . "LAZYCLOSE=$lazyClose;"
             . "MaxFieldLength=$maxFieldLength;"
             . "PREFETCH=$prefetch;"
             . "QUERYTIMEOUT=$queryTimeout;"
             // Modules settings
             . "DefaultPkgLibrary=$defaultPkgLibrary;"
             . "DefaultPackage=$defaultPackage;"
             . "ExtendedDynamic=$extendedDynamic;"
             // Diagnostic settings
             . "QAQQINILibrary=$QAQQINILibrary;"
             . "SQDIAGCODE=$sqDiagCode;"
             // Sort settings
             . "LANGUAGEID=$languageId;"
             . "SORTTABLE=$sortTable;"
             . "SortSequence=$sortSequence;"
             . "SORTWEIGHT=$sortWeight;"
             // Conversion settings
             . "AllowUnsupportedChar=$allowUnsupportedChar;"
             . "CCSID=$ccsid;"
             . "GRAPHIC=$graphic;"
             . "ForceTranslation=$forceTranslation;"
             // Other settings
             . "ALLOWPROCCALLS=$allowProcCalls;"
             . "DB2SQLSTATES=$DB2SqlStates;"
             . "DEBUG=$debug;"
             . "TRUEAUTOCOMMIT=$trueAutoCommit;"
             . "CATALOGOPTIONS=$catalogOptions;"
             . "LibraryView=$libraryView;"
             . "ODBCRemarks=$ODBCRemarks;"
             . "SEARCHPATTERN=$searchPattern;"
             . "TranslationDLL=$translationDLL;"
             . "TranslationOption=$translationOption;"
             . "MAXTRACESIZE=$maxTraceSize;"
             . "MultipleTraceFiles=$multipleTraceFiles;"
             . "TRACE=$trace;"
             . "TRACEFILENAME=$traceFilename;"
             . "ExtendedColInfo=$extendedColInfo;"
             ;

        return $dsn;
    }

}

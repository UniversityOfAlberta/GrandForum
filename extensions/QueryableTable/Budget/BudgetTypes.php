<?php

autoload_register('QueryableTable/Budget/Cells');
autoload_register('QueryableTable/Budget/Arrays');

//CellTypes
////Basic Types
define('MONEY', 0);
define('PERC', 1);
define('TOTAL', 2);
define('ROW_TOTAL', 3);
define('COL_TOTAL', 4);
define('ROW_SUM', 5);
define('COL_SUM', 6);
define('CUBE_ROW_TOTAL', 25);
define('CUBE_COL_TOTAL', 26);
define('CUBE_TOTAL', 27);
define('SUB_MONEY', 28);
define('SUB_PERC', 29);
define('HEAD_MONEY', 30);
define('INTEGER', 31);
////Validation Types
define('V_PROJ', 50);
define('V_PERS', 51);
define('V_PERS_NOT_NULL', 52);

$cellTypes[MONEY] = "MoneyCell";
$cellTypes[INTEGER] = "IntegerCell";
$cellTypes[SUB_MONEY] = "SubMoneyCell";
$cellTypes[HEAD_MONEY] = "HeadMoneyCell";
$cellTypes[PERC] = "PercCell";
$cellTypes[SUB_PERC] = "PercCell";
$cellTypes[TOTAL] = "TotalCell";
$cellTypes[ROW_TOTAL] = "RowTotalCell";
$cellTypes[COL_TOTAL] = "ColTotalCell";
$cellTypes[ROW_SUM] = "RowSumCell";
$cellTypes[COL_SUM] = "ColSumCell";
$cellTypes[CUBE_ROW_TOTAL] = "CubeRowTotalCell";
$cellTypes[CUBE_COL_TOTAL] = "CubeColTotalCell";
$cellTypes[CUBE_TOTAL] = "CubeTotalCell";
$cellTypes[V_PROJ] = "VProjCell";
$cellTypes[V_PERS] = "VPersCell";
$cellTypes[V_PERS_NOT_NULL] = "VPersNotNullCell";

//Budget Structures
define('NETWORK_BUDGET_STRUCTURE', 0);
define('SUPPLEMENTAL_STRUCTURE', 1);
define('REPORT_STRUCTURE', 2);
define('REPORT2_STRUCTURE', 3);
define('GLYCONET_BUDGET_STRUCTURE', 4);
define('AGEWELL_BUDGET_STRUCTURE', 5);
define('TVN_BUDGET_STRUCTURE', 6);
define('AGEWELL_NI_BUDGET_STRUCTURE', 7);
define('TECH_WORKSHOP_STRUCTURE', 8);
define('REGIONAL_MEETING_STRUCTURE', 9);
define('TVN_CATALYST_STRUCTURE', 10);
define('FES_BUDGET_STRUCTURE', 11);
define('FES_THEME_BUDGET_STRUCTURE', 12);
define('FES_NI_BUDGET_STRUCTURE', 13); // Not really NIs, it is the Equipment
define('FES_EQUIPMENT_STRUCTURE', 14);
define('FES_EXTERNAL_STRUCTURE', 15);
define('AGEWELL_NI_BUDGET_STRUCTURE2', 16);
define('HUAWEI_BUDGET', 17);
define('HUAWEI_MILESTONES', 18);
define('AGEWELL_BUDGET_STRUCTURE2020', 19);
define('AGEWELL_NI_BUDGET_STRUCTURE2020', 20);
define('GLYCONET_NI_BUDGET_STRUCTURE', 21);
define('GLYCONET_BUDGET_STRUCTURE2', 22);
define('GLYCONET_NI_BUDGET_STRUCTURE2', 23);
define('AGEWELL_BUDGET_STRUCTURE2022', 24);
define('AGEWELL_NI_BUDGET_STRUCTURE2022', 25);
define('AGEWELL_BUDGET_STRUCTURE_IH', 26);
define('GLYCONET_KPI_STRUCTURE', 27);

$budgetStructures = array();

$budgetStructures[NETWORK_BUDGET_STRUCTURE] = array(array());

$budgetStructures[FES_BUDGET_STRUCTURE] = 
    array(array(NA, HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(NA, HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(NA, HEAD2_ROW.('(1.1 Postdoctoral)'),  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD2_ROW.('(1.2 Doctorate)'),  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD2_ROW.('(1.3 Masters)'),  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD2_ROW.('(1.4 Bachelors)'),  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD2_ROW.('(1.5 Research Associates)'),  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD2_ROW.('(1.6 Research Technical Support)'),  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD2_ROW.('(1.7 Research Admin Support)'),  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD2_ROW.('(1.8 Professional and Technical Services)'),  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD2_ROW.('(1.9 Other Misc. Compensation Expenses)'),  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD1_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD1_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(NA, HEAD2_ROW.('(4.1 Equipment)'),  MONEY, MONEY, MONEY, MONEY, MONEY, MONEY, MONEY),
          array(NA, HEAD2_ROW.('(4.2 Supplies)'),  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD1_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD1_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD1_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD1_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(NA, HEAD2_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(NA, HEAD2_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(NA, HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
    );
    
$budgetStructures[FES_NI_BUDGET_STRUCTURE] = 
    array(array(HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
    );
    
$budgetStructures[FES_EQUIPMENT_STRUCTURE] = 
    array(array(HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
    );
    
$budgetStructures[FES_EXTERNAL_STRUCTURE] = 
    array(array(NA, HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(NA, NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA, NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA, BLANK,      BLANK,      BLANK,      BLANK,      BLANK,      BLANK,      BLANK,      BLANK),
          array(NA, HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, WRAP,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(NA, HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY)
    );
    
$budgetStructures[FES_THEME_BUDGET_STRUCTURE] = 
    array(array(HEAD,       HEAD,       HEAD,       HEAD,       HEAD,       HEAD,       HEAD,       HEAD),
          array(HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
    );

$budgetStructures[GLYCONET_BUDGET_STRUCTURE] =
    array(array(HEAD1,      READ,            NA,          NA,          NA,          BLANK,  BLANK,  BLANK,      BLANK,       BLANK,       BLANK,       BLANK,       BLANK,       BLANK,       BLANK,       BLANK),
          array(HEAD1,      V_PERS_NOT_NULL, NA,          NA,          NA,          BLANK,  BLANK,  BLANK,      BLANK,       BLANK,       BLANK,       BLANK,       BLANK,       BLANK,       BLANK,       BLANK),
          array(HEAD1,      READ,            NA,          NA,          NA,          BLANK,  BLANK,  BLANK,      BLANK,       BLANK,       BLANK,       BLANK,       BLANK,       BLANK,       BLANK,       BLANK),
          array(BLANK,      NA,              NA,          NA,          NA,          NA,     NA,     NA,         NA,          NA,          NA,          NA,          NA,          NA,          NA,          NA),
          array(HEAD1_ROW,  NA,              NA,          NA,          NA,          BLANK,  NA,     HEAD1_ROW,  NA,          NA,          NA,          NA,          NA,          NA,          NA,          NA),
          array(HEAD1,      HEAD,            HEAD,        HEAD,        HEAD,        BLANK,  NA,     HEAD1,      HEAD,        NA,          HEAD,        NA,          HEAD,        NA,          HEAD,        NA),
          array(HEAD1,      HEAD,            HEAD,        HEAD,        HEAD,        BLANK,  NA,     HEAD1,      HEAD,        HEAD,        HEAD,        HEAD,        HEAD,        HEAD,        HEAD,        HEAD),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY,       MONEY),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY),
          array(NA,         NA,              NA,          NA,          NA,          NA,     NA,     NA,         NA,          NA,          NA,          NA,          NA,          NA,          NA,         NA),
          array(NA,         NA,              NA,          NA,          NA,          NA,     NA,     NA,         NA,          NA,          NA,          NA,          NA,          NA,          NA,         NA),
          array(NA,         NA,              NA,          NA,          NA,          NA,     NA,     NA,         NA,          NA,          NA,          NA,          NA,          NA,          NA,         NA),
          array(BLANK,      NA,              NA,          NA,          NA,          NA,     NA,     NA,         NA,          NA,          NA,          NA,          NA,          NA,          NA,         NA),
          array(HEAD1_ROW,  NA,              NA,          NA,          NA,          BLANK,  NA,     HEAD1_ROW,  NA,          NA,          NA,          NA,          BLANK,       NA,          NA,         NA),
          array(HEAD1,      HEAD,            HEAD,        HEAD,        HEAD,        BLANK,  NA,     HEAD1,      HEAD,        HEAD,        HEAD,        HEAD,        BLANK,       NA,          NA,         NA),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,       NA,          NA,         NA),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD4,      MONEY,           MONEY,       MONEY,       MONEY,       BLANK,  NA,     HEAD4,      MONEY,       MONEY,       MONEY,       MONEY,       BLANK,       NA,          NA,         NA),
          array(HEAD1_ROW,  HEAD_MONEY,      HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,  NA,     HEAD1_ROW,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  HEAD_MONEY,  BLANK,       NA,          NA,         NA),
    );
    
$budgetStructures[GLYCONET_NI_BUDGET_STRUCTURE] = $budgetStructures[GLYCONET_BUDGET_STRUCTURE];

$budgetStructures[GLYCONET_BUDGET_STRUCTURE2] = 
    array(array(HEAD1,      READ,       NA,         NA,     NA,         NA,         NA),
          array(HEAD1,      READ,       NA,         NA,     NA,         NA,         NA),
          array(HEAD1,      READ,       NA,         NA,     NA,         NA,         NA),
          array(BLANK,      NA,         NA,         NA,     NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         BLANK,      NA,     HEAD1_ROW,  NA,         NA),
          array(HEAD1,      HEAD,       BLANK,      NA,     HEAD1,      HEAD,       NA),
          array(HEAD1,      HEAD,       BLANK,      NA,     HEAD1,      HEAD,       HEAD),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,  MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,  MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,  MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,  MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,  MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,  MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,  MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,  MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,  MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,  MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(NA,         NA,         NA,         NA,     NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,     NA,         NA,         NA),
          array(BLANK,      NA,         NA,         NA,     NA,         NA,         NA),
          array(BLANK,      NA,         NA,         NA,     NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         BLANK,      NA,     HEAD1_ROW,  NA,         BLANK),
          array(HEAD1,      HEAD,       BLANK,      NA,     HEAD1,      HEAD,       BLANK),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, BLANK),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD2,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      BLANK),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, BLANK),
    );

$budgetStructures[GLYCONET_NI_BUDGET_STRUCTURE2] = 
    array(array(HEAD1,      READ,       NA,         NA,         NA,         NA,         NA,         NA,         NA,     NA,         NA,         NA),
          array(HEAD1,      READ,       NA,         NA,         HEAD1,      NA,         NA,         NA,         NA,     NA,         NA,         NA),
          array(HEAD1,      READ,       NA,         BLANK,      HEAD1,      READ,       NA,         BLANK,      NA,     HEAD1,      READ,       NA),
          array(BLANK,      NA,         BLANK,      NA,         NA,         NA,         NA,         NA,         NA,     NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         BLANK,      NA,         HEAD1_ROW,  NA,         NA,         BLANK,      NA,     HEAD1_ROW,  NA,         NA),
          array(HEAD1,      HEAD,       BLANK,      NA,         HEAD1,      HEAD,       NA,         BLANK,      NA,     HEAD1,      HEAD,       NA),
          array(HEAD1,      HEAD,       BLANK,      NA,         HEAD1,      HEAD,       HEAD,       BLANK,      NA,     HEAD1,      HEAD,       HEAD),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,         HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  HEAD_MONEY, BLANK,      NA,         HEAD2_ROW,  HEAD_MONEY, HEAD_MONEY, BLANK,      NA,     HEAD2_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD2_ROW,  HEAD_MONEY, BLANK,      NA,         HEAD2_ROW,  HEAD_MONEY, HEAD_MONEY, BLANK,      NA,     HEAD2_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD2_ROW,  HEAD_MONEY, BLANK,      NA,         HEAD2_ROW,  HEAD_MONEY, HEAD_MONEY, BLANK,      NA,     HEAD2_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD2_ROW,  HEAD_MONEY, BLANK,      NA,         HEAD2_ROW,  HEAD_MONEY, HEAD_MONEY, BLANK,      NA,     HEAD2_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD3,      MONEY,      BLANK,      NA,         HEAD3,      MONEY,      MONEY,      BLANK,      NA,     HEAD3,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,         HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,         HEAD2,      MONEY,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,         HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,         HEAD2,      MONEY,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,         HEAD2,      MONEY,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,         HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,         HEAD2,      MONEY,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,         HEAD2,      MONEY,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      MONEY),
          array(HEAD2,      MONEY,      BLANK,      NA,         HEAD2,      MONEY,      MONEY,      BLANK,      NA,     HEAD2,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_MONEY, BLANK,      NA,         HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, BLANK,      NA,     HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY),
    );
    
$budgetStructures[GLYCONET_KPI_STRUCTURE] = 
    array(array(NA,         NA,         NA),
          array(NA,         NA,         NA),
          array(NA,         HEAD,       NA),            // 1. Users of the Facility
          array(NA,         CENTRE,     CENTRE),
          array(NA,         HEAD_ROW,   NA),            // Total Users by Type
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT_BOLD, RIGHT_BOLD),
          array(NA,         HEAD_ROW,   NA),            // Total Users Geographically
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT_BOLD, RIGHT_BOLD),
          array(NA,         HEAD_ROW,   NA),            // Total Users by Sector
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT_BOLD, RIGHT_BOLD),
          array(NA,         BLANK,      NA),
          array(NA,         HEAD,       NA),            // 2. User Demand for Facility
          array(NA,         CENTRE,     CENTRE),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         BLANK,      NA),
          array(NA,         HEAD,       NA),            // 3. Optimal User of the Facility
          array(NA,         CENTRE,     CENTRE),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         BLANK,      NA),
          array(NA,         HEAD,       NA),            // 4. Management FTEs Contributing to O&M
          array(NA,         CENTRE,     CENTRE),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT_BOLD, RIGHT_BOLD),
          array(NA,         BLANK,      NA),
          array(NA,         HEAD,       NA),            // 5. User Satisfaction
          array(NA,         CENTRE,     CENTRE),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         BLANK,      NA),
          array(NA,         HEAD,       NA),            // 6. Research Outputs
          array(NA,         CENTRE,     CENTRE),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT_BOLD, RIGHT_BOLD),
          array(NA,         BLANK,      NA),
          array(NA,         HEAD,       NA),            // 7. Engagement and Outreach Activities
          array(NA,         CENTRE,     CENTRE),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT_BOLD, RIGHT_BOLD),
          array(NA,         BLANK,      NA),
          array(NA,         HEAD,       NA),            // 8. Contributions to Training (By HQP Type)
          array(NA,         CENTRE,     CENTRE),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT_BOLD, RIGHT_BOLD),
          array(NA,         BLANK,      NA),
          array(NA,         HEAD,       NA),            // 9. Technology Development and Transfer
          array(NA,         CENTRE,     CENTRE),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT_BOLD, RIGHT_BOLD),
          array(NA,         BLANK,      NA),
          array(NA,         HEAD,       NA),            // 10. Facility Specific Indicators
          array(NA,         CENTRE,     CENTRE),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT),
          array(NA,         RIGHT,      RIGHT)
    );
    
$budgetStructures[TECH_WORKSHOP_STRUCTURE] = 
    array(array(HEAD_ROW,   NA,         NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD."(&nbsp;)",        NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  HEAD1_ROW,  HEAD1_ROW,  NA,         NA),
          array(HEAD4,      MONEY,      READ,       NA,         NA),
          array(HEAD4,      MONEY,      READ,       NA,         NA),
          array(HEAD4,      MONEY,      READ,       NA,         NA),
          array(HEAD4,      MONEY,      READ,       NA,         NA),
          array(HEAD4,      MONEY,      READ,       NA,         NA),
          array(HEAD4,      MONEY,      READ,       NA,         NA),
          array(HEAD4,      MONEY,      READ,       NA,         NA),
          array(HEAD1_ROW,  HEAD1_ROW,  NA,         NA,         NA),
          array(HEAD4,      READ,       READ,       READ,       READ),
          array(HEAD4,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD4,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD4,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW."(&nbsp;)",   HEAD1_ROW,  HEAD1_ROW,  HEAD1_ROW,  HEAD1_ROW),
          array(HEAD4,      COL_TOTAL,  BLANK,      NA,         NA));
          
$budgetStructures[REGIONAL_MEETING_STRUCTURE] = 
    array(array(HEAD_ROW,   NA,         NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD4,      READ,       NA,         NA,         NA),
          array(HEAD."(&nbsp;)",        NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  HEAD1_ROW,  HEAD1_ROW,  HEAD1_ROW,  NA),
          array(HEAD4,      MONEY,      MONEY,      READ,       NA),
          array(HEAD4,      MONEY,      MONEY,      READ,       NA),
          array(HEAD4,      MONEY,      MONEY,      READ,       NA),
          array(HEAD4,      MONEY,      MONEY,      READ,       NA),
          array(HEAD4,      MONEY,      MONEY,      HEAD1_ROW,  NA),
          array(HEAD1_ROW,  HEAD1_ROW,  NA,         NA,         NA),
          array(HEAD4,      MONEY,      MONEY,      READ,       NA),
          array(HEAD4,      MONEY,      MONEY,      READ,       NA),
          array(HEAD4,      MONEY,      MONEY,      HEAD1_ROW,  NA),
          array(HEAD1_ROW,  HEAD1_ROW,  NA,         NA,         NA),
          array(HEAD4,      MONEY,      MONEY,      READ,       NA),
          array(HEAD4,      MONEY,      MONEY,      READ,       NA),
          array(HEAD4,      MONEY,      MONEY,      READ,       NA),
          array(HEAD4,      MONEY,      MONEY,      HEAD1_ROW,  NA),
          array(HEAD1_ROW."(&nbsp;)",   HEAD1_ROW,  NA,         NA,         NA),
          array(HEAD4,      COL_TOTAL,  COL_TOTAL,  HEAD1_ROW,  NA));
    
$budgetStructures[AGEWELL_BUDGET_STRUCTURE] =
    array(array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD_ROW,   HEAD_ROW,   NA,         NA,         HEAD_ROW,   NA,         NA,         HEAD_ROW),
          array(HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       COL_SUM,    COL_TOTAL,  COL_SUM,    COL_SUM,    COL_SUM,    COL_SUM)
    );
    
$budgetStructures[AGEWELL_BUDGET_STRUCTURE2020] =
    array(array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD_ROW,   HEAD_ROW,   NA,         NA,         HEAD_ROW,   NA,         NA,         HEAD_ROW),
          array(HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1_ROW,  HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       COL_SUM,    COL_TOTAL,  COL_SUM,    COL_SUM,    COL_SUM,    COL_SUM)
    );
    
$budgetStructures[AGEWELL_BUDGET_STRUCTURE2022] =
    array(array(NA,         NA,            NA,            NA,            NA,         NA,         NA,         NA),
          array(NA,         NA,            NA,            NA,            NA,         NA,         NA,         NA),
          array(NA,         NA,            NA,            NA,            NA,         NA,         NA,         NA),
          array(NA,         NA,            NA,            NA,            NA,         NA,         NA,         NA),
          array(NA,         NA,            NA,            NA,            NA,         NA,         NA,         NA),
          array(NA,         NA,            NA,            NA,            NA,         NA,         NA,         NA),
          array(HEAD_ROW,   WRAP_HEAD_ROW, WRAP_HEAD_ROW, WRAP_HEAD_ROW, HEAD_ROW,   NA,         HEAD_ROW,   NA),
          array(HEAD_ROW,   HEAD_ROW,      HEAD_ROW,      HEAD_ROW,      HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1_ROW,  HEAD_ROW,      NA,            NA,            NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_ROW,      NA,            NA,            NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  COL_SUM,       COL_SUM,       COL_TOTAL,     COL_SUM,    COL_SUM,    COL_SUM,    COL_SUM)
    );
    
$budgetStructures[AGEWELL_BUDGET_STRUCTURE_IH] =
    array(array(NA,         NA,            NA,            NA,            NA,            NA,         NA,         NA),
          array(NA,         NA,            NA,            NA,            NA,            NA,         NA,         NA),
          array(NA,         NA,            NA,            NA,            NA,            NA,         NA,         NA),
          array(NA,         NA,            NA,            NA,            NA,            NA,         NA,         NA),
          array(NA,         NA,            NA,            NA,            NA,            NA,         NA,         NA),
          array(HEAD_ROW,   WRAP_HEAD_ROW, NA,            NA,            WRAP_HEAD_ROW, NA,         NA,         HEAD_ROW),
          array(HEAD_ROW,   HEAD_ROW,      HEAD_ROW,      HEAD_ROW,      HEAD_ROW,      HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1_ROW,  HEAD_ROW,      HEAD_ROW,      HEAD_ROW,      HEAD_ROW,      HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD2_ROW,  MONEY,         MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,         MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,         MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_ROW,      HEAD_ROW,      HEAD_ROW,      HEAD_ROW,      HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD2_ROW,  MONEY,         MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,         MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,         MONEY,         MONEY,         MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  COL_SUM,       COL_SUM,       COL_TOTAL,     COL_SUM,       COL_SUM,    COL_SUM,    COL_SUM)
    );
    
$budgetStructures[AGEWELL_NI_BUDGET_STRUCTURE] =
    array(array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      HEAD_ROW.'(Project Investigator)',  NA,         V_PERS_NOT_NULL,        NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      HEAD_ROW.'(University)',            NA,         READ,       NA,         NA,         NA,         NA),
          array(BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD_ROW,   HEAD_ROW,   NA,         NA,         HEAD_ROW,   NA,         NA,         HEAD_ROW),
          array(HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       COL_SUM,    COL_TOTAL,  COL_SUM,    COL_SUM,    COL_SUM,    COL_SUM)
    );
    
$budgetStructures[AGEWELL_NI_BUDGET_STRUCTURE2] =
    array(array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      HEAD_ROW.'(Project Investigator)',  NA,         V_PERS_NOT_NULL,        NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      HEAD_ROW.'(University)',            NA,         READ,       NA,         NA,         NA,         NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD_ROW,   HEAD_ROW,   NA,         NA,         HEAD_ROW,   NA,         NA,         HEAD_ROW),
          array(HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       COL_SUM,    COL_TOTAL,  COL_SUM,    COL_SUM,    COL_SUM,    COL_SUM)
    );
    
$budgetStructures[AGEWELL_NI_BUDGET_STRUCTURE2020] =
    array(array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD_ROW.'(Name of project Network Investigator Receiving Funding:)',   READ,       NA,       NA,         NA,         NA,         NA,   NA),
          array(HEAD_ROW.'(Home Institution of project Network Investigator:)',         READ,       NA,       NA,         NA,         NA,         NA,   NA),
          array(NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD_ROW,   HEAD_ROW,   NA,         NA,         HEAD_ROW,   NA,         NA,         HEAD_ROW),
          array(HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1_ROW,  HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  READ,       COL_SUM,    COL_TOTAL,  COL_SUM,    COL_SUM,    COL_SUM,    COL_SUM)
    );
    
$budgetStructures[AGEWELL_NI_BUDGET_STRUCTURE2022] =
    array(array(NA,         NA,            NA,         NA,         NA,         NA),
          array(NA,         NA,            NA,         NA,         NA,         NA),
          array(HEAD_ROW.'(Name of project Network Investigator Receiving Funding:)',   READ,       NA,       NA,         NA,         NA),
          array(HEAD_ROW.'(Home Institution of project Network Investigator:)',         READ,       NA,       NA,         NA,         NA),
          array(BLANK,      NA,            NA,         NA,         NA,         NA),
          array(HEAD_ROW,   WRAP_HEAD_ROW, HEAD_ROW,   NA,         HEAD_ROW,   NA),
          array(HEAD_ROW,   HEAD_ROW,      HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1_ROW,  HEAD_ROW,      NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  HEAD_ROW,      NA,         NA,         NA,         NA),
          array(HEAD2_ROW,  MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD2_ROW,  MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  MONEY,         MONEY,      MONEY,      MONEY,      MONEY),
          array(HEAD1_ROW,  COL_SUM,       COL_SUM,    COL_TOTAL,  COL_SUM,    COL_SUM)
    );
    
$budgetStructures[TVN_BUDGET_STRUCTURE] =
    array(array(HEAD,       NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         READ,       NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         V_PERS,     NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         V_PERS,     NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         STRING,     NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         STRING,     NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         STRING,     NA,         NA,         NA,         NA,         NA),
          array(BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  MONEY,      NA,         BLANK,      NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  MONEY,      NA,         BLANK,      NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  MONEY,      NA,         BLANK,      NA,         NA,         NA,         NA),
          array(BLANK,      HEAD_ROW,   NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   NA,         HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1_ROW,  HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1,      READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY),
          array(HEAD1,      READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY),
          array(HEAD1,      READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY),
          array(HEAD1_ROW,  HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD1,      READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY),
          array(HEAD1,      READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY),
          array(HEAD1_ROW,  HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD1,      HEAD1_ROW,  MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY),
          array(HEAD1,      HEAD1_ROW,  MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY),
          array(HEAD1,      HEAD1_ROW,  MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY),
          array(HEAD1,      HEAD1_ROW,  MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY),
          array(HEAD1_ROW,  HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD1_ROW,  HEAD1_ROW,  HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY)
    );
    
$budgetStructures[TVN_CATALYST_STRUCTURE] =
    array(array(HEAD,       NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         READ,       NA,         NA,         NA,         NA,         NA,         NA,         BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         STRING,     NA,         NA,         NA,         NA,         NA,         NA,         BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         STRING,     NA,         NA,         NA,         NA,         NA,         NA,         BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         STRING,     NA,         NA,         NA,         NA,         NA,         NA,         BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD1_ROW,  NA,         STRING,     NA,         NA,         NA,         NA,         NA,         NA,         BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(BLANK,      BLANK,      HEAD,       NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA,         NA),
          array(HEAD,       NA,         HEAD,       NA,         NA,         HEAD,       NA,         NA,         HEAD_ROW,   HEAD,       NA,         NA,         HEAD,       NA,         NA,         HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD1_ROW,  HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD1_ROW,  HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW,   HEAD_ROW),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD2_ROW,  READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD1_ROW,  HEAD_ROW,   HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD1_ROW,  READ,       MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, MONEY,      MONEY,      HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY),
          array(HEAD1_ROW,  HEAD_ROW,   HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY, HEAD_MONEY)
    );

$budgetStructures[SUPPLEMENTAL_STRUCTURE] =
    array(array(HEAD1,  V_PERS_NOT_NULL, BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD1,  V_PROJ, V_PROJ, V_PROJ, V_PROJ, V_PROJ, V_PROJ, BLANK,  BLANK,  BLANK),
          array(NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA),
          array(NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA),
          array(NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA),
          array(NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA,     NA),
          array(HEAD1,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  HEAD1,  HEAD1,  HEAD1),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD1,  COL_SUM,COL_SUM,COL_SUM,COL_SUM,COL_SUM,COL_SUM,ROW_SUM,PERC,   PERC)
    );
    
$budgetStructures[REPORT2_STRUCTURE] =
    array(array(HEAD1,  V_PERS_NOT_NULL, BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD1,  V_PROJ,          V_PROJ, V_PROJ, V_PROJ, V_PROJ, V_PROJ, BLANK,   BLANK),
          array(HEAD1,  V_PERS,          V_PERS, V_PERS, V_PERS, V_PERS, V_PERS, BLANK,   BLANK),
          array(HEAD1,  V_PERS,          V_PERS, V_PERS, V_PERS, V_PERS, V_PERS, BLANK,   BLANK),
          array(HEAD1,  V_PERS,          V_PERS, V_PERS, V_PERS, V_PERS, V_PERS, BLANK,   BLANK),
          array(NA,     NA,              NA,     NA,     NA,     NA,     NA,     NA,      NA),
          array(HEAD1,  BLANK,           BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  HEAD1,   HEAD1),
          array(HEAD2,  SUB_MONEY."(7, 7)",  SUB_MONEY."(7, 7)",  SUB_MONEY."(7, 7)",  SUB_MONEY."(7, 7)",  SUB_MONEY."(7, 7)",  SUB_MONEY."(7, 7)",  SUB_MONEY, SUB_PERC."(80)"),
          array(HEAD3,  MONEY."(8, 7)",      MONEY."(8, 7)",      MONEY."(8, 7)",      MONEY."(8, 7)",      MONEY."(8, 7)",      MONEY."(8, 7)",      ROW_SUM,   PERC."(53)"),
          array(HEAD3,  MONEY."(9, 7)",      MONEY."(9, 7)",      MONEY."(9, 7)",      MONEY."(9, 7)",      MONEY."(9, 7)",      MONEY."(9, 7)",      ROW_SUM,   PERC."(14)"),
          array(HEAD3,  MONEY."(10, 7)",     MONEY."(10, 7)",     MONEY."(10, 7)",     MONEY."(10, 7)",     MONEY."(10, 7)",     MONEY."(10, 7)",     ROW_SUM,   PERC."(6)"),
          array(HEAD3,  MONEY."(11, 7)",     MONEY."(11, 7)",     MONEY."(11, 7)",     MONEY."(11, 7)",     MONEY."(11, 7)",     MONEY."(11, 7)",     ROW_SUM,   PERC."(7)"),
          array(HEAD2,  SUB_MONEY."(12, 7)", SUB_MONEY."(12, 7)", SUB_MONEY."(12, 7)", SUB_MONEY."(12, 7)", SUB_MONEY."(12, 7)", SUB_MONEY."(12, 7)", SUB_MONEY, SUB_PERC."(5)"),
          array(HEAD3,  MONEY."(13, 7)",     MONEY."(13, 7)",     MONEY."(13, 7)",     MONEY."(13, 7)",     MONEY."(13, 7)",     MONEY."(13, 7)",     ROW_SUM,   PERC."(2)"),
          array(HEAD3,  MONEY."(14, 7)",     MONEY."(14, 7)",     MONEY."(14, 7)",     MONEY."(14, 7)",     MONEY."(14, 7)",     MONEY."(14, 7)",     ROW_SUM,   PERC."(1)"),
          array(HEAD3,  MONEY."(15, 7)",     MONEY."(15, 7)",     MONEY."(15, 7)",     MONEY."(15, 7)",     MONEY."(15, 7)",     MONEY."(15, 7)",     ROW_SUM,   PERC."(2)"),
          array(HEAD2,  MONEY."(16, 7)",     MONEY."(16, 7)",     MONEY."(16, 7)",     MONEY."(16, 7)",     MONEY."(16, 7)",     MONEY."(16, 7)",     ROW_SUM,   PERC."(2)"),
          array(HEAD2,  MONEY."(17, 7)",     MONEY."(17, 7)",     MONEY."(17, 7)",     MONEY."(17, 7)",     MONEY."(17, 7)",     MONEY."(17, 7)",     ROW_SUM,   PERC."(4)"),
          array(HEAD2,  SUB_MONEY."(18, 7)", SUB_MONEY."(18, 7)", SUB_MONEY."(18, 7)", SUB_MONEY."(18, 7)", SUB_MONEY."(18, 7)", SUB_MONEY."(18, 7)", SUB_MONEY, SUB_PERC."(9)"),
          array(HEAD3,  MONEY."(19, 7)",     MONEY."(19, 7)",     MONEY."(19, 7)",     MONEY."(19, 7)",     MONEY."(19, 7)",     MONEY."(19, 7)",     ROW_SUM,   PERC."(3)"),
          array(HEAD3,  MONEY."(20, 7)",     MONEY."(20, 7)",     MONEY."(20, 7)",     MONEY."(20, 7)",     MONEY."(20, 7)",     MONEY."(20, 7)",     ROW_SUM,   PERC."(3)"),
          array(HEAD3,  MONEY."(21, 7)",     MONEY."(21, 7)",     MONEY."(21, 7)",     MONEY."(21, 7)",     MONEY."(21, 7)",     MONEY."(21, 7)",     ROW_SUM,   PERC."(3)"),
          array(HEAD1,  COL_SUM,             COL_SUM,             COL_SUM,             COL_SUM,             COL_SUM,             COL_SUM,             ROW_SUM,   PERC)
    );
    
$budgetStructures[REPORT_STRUCTURE] =
    array(array(HEAD1,  V_PERS_NOT_NULL,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK, BLANK,  BLANK,  BLANK),
          array(HEAD1,  V_PROJ, V_PROJ, V_PROJ, V_PROJ, V_PROJ, V_PROJ, BLANK,  BLANK,  BLANK),
          array(HEAD1,  V_PERS, V_PERS, V_PERS, V_PERS, V_PERS, V_PERS, BLANK,  BLANK,  BLANK),
          array(HEAD1,  V_PERS, V_PERS, V_PERS, V_PERS, V_PERS, V_PERS, BLANK,  BLANK,  BLANK),
          array(HEAD1,  V_PERS, V_PERS, V_PERS, V_PERS, V_PERS, V_PERS, BLANK,  BLANK,  BLANK),
          //YEAR1
          array(BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD1,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  HEAD1,  HEAD1,  HEAD1),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD1,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          //YEAR2
          array(BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD1,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  HEAD1,  HEAD1,  HEAD1),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD1,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          //YEAR3
          array(BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD1,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  HEAD1,  HEAD1,  HEAD1),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD2,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK,  BLANK),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD3,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
          array(HEAD1,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  MONEY,  ROW_SUM,PERC,   PERC),
    );
    
$budgetStructures[HUAWEI_BUDGET] =
    array(array(HEAD,      HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW),
          array(HEAD1_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW),
          array(HEAD2,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD2,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD2,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW),
          array(HEAD2,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD2,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1,     COL_SUM,  COL_SUM,  COL_SUM,  COL_SUM),
    );
    
$budgetStructures[HUAWEI_MILESTONES] =
    array(array(HEAD,      HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW),
          array(HEAD1_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW),
          array(HEAD2,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD2,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD2,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW, HEAD_ROW),
          array(HEAD2,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD2,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1,     MONEY,    MONEY,    MONEY,    MONEY),
          array(HEAD1,     COL_SUM,  COL_SUM,  COL_SUM,  COL_SUM),
    );
    
?>

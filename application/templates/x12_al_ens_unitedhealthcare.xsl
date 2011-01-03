<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" indent="yes"/>

<xsl:variable name="lowercase" select="'abcdefghijklmnopqrstuvwxyz'"/>
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>

<!-- MAIN: TEMPLATE START -->
<xsl:template match="/">
<x12_al_ens_unitedhealthcare>
  <xsl:apply-templates select="data/ISA"/>
  <xsl:apply-templates select="data/GS"/>
  <!--<xsl:value-of select="current()"/>-->
  <xsl:for-each select="data/data">
    <xsl:apply-templates select="HL"/>
  </xsl:for-each>
  <xsl:apply-templates select="data/footer"/>
  <!-- footer -->
  <SE.1>SE*</SE.1>
  <SE.2>*</SE.2>
  <SE.3>0001~</SE.3>
  <GE.1>GE*</GE.1>
  <GE.2>1*</GE.2>
  <GE.3><xsl:value-of select="data/GS/claim/claim_id"/>~</GE.3>
  <IEA.1>IEA*</IEA.1>
  <IEA.2>1*</IEA.2>
  <IEA.3><xsl:call-template name="str-pad-left">
      <xsl:with-param name="input" select="data/GS/claim/claim_id"/>
      <xsl:with-param name="string" select="'0'"/>
      <xsl:with-param name="length" select="'9'"/>
    </xsl:call-template>~</IEA.3>
</x12_al_ens_unitedhealthcare>
</xsl:template>
<!-- MAIN: TEMPLATE END -->


<!-- TEMPLATE: HL START -->
<xsl:template match="HL">
  <HL2000A.1>HL*</HL2000A.1>
  <HL2000A.2><xsl:value-of select="hlCount" />**</HL2000A.2>
  <HL2000A.3>20*</HL2000A.3>
  <HL2000A.4>1~</HL2000A.4>

  <HL2010AA.1>NM1*</HL2010AA.1>
  <HL2010AA.2>85*</HL2010AA.2>
  <HL2010AA.3>2*</HL2010AA.3>
  <HL2010AA.4><xsl:value-of select="translate(practice/name, $lowercase, $uppercase)"/>*</HL2010AA.4>
  <HL2010AA.5>****</HL2010AA.5>
  <HL2010AA.6><xsl:value-of select="practice/identifier_type"/>*</HL2010AA.6>
  <HL2010AA.7><xsl:value-of select="translate(practice/identifier, $lowercase, $uppercase)"/>~</HL2010AA.7>
  <HL2010AA.8>N3*</HL2010AA.8>
  <HL2010AA.9><xsl:value-of select="translate(practice/print_address, $lowercase, $uppercase)"/>~</HL2010AA.9>
  <HL2010AA.10>N4*</HL2010AA.10>
  <HL2010AA.11><xsl:value-of select="translate(practice/city, $lowercase, $uppercase)"/>*</HL2010AA.11>
  <HL2010AA.12><xsl:value-of select="translate(practice/state, $lowercase, $uppercase)"/>*</HL2010AA.12>
  <HL2010AA.13><xsl:call-template name="str-pad-right">
      <xsl:with-param name="input" select="practice/zip"/>
      <xsl:with-param name="length" select="'5'"/>
    </xsl:call-template>~</HL2010AA.13>
  <HL2010AA.14>REF*</HL2010AA.14>
  <HL2010AA.15>1C*</HL2010AA.15>
  <HL2010AA.16><xsl:value-of select="translate(treating_facility/identifier, $lowercase, $uppercase)"/>~</HL2010AA.16>
  <HL2010AA.17>PER*</HL2010AA.17>
  <HL2010AA.18>IC*</HL2010AA.18>
  <HL2010AA.19><xsl:value-of select="translate(billing_contact/name, $lowercase, $uppercase)"/>*</HL2010AA.19>
  <HL2010AA.20>TE*</HL2010AA.20>
  <HL2010AA.21><xsl:value-of select="billing_contact/phone_number"/>~</HL2010AA.21>

  <HL2010AB.1>NM1*</HL2010AB.1>
  <HL2010AB.2>87*</HL2010AB.2>
  <HL2010AB.3>2*</HL2010AB.3>
  <HL2010AB.4><xsl:value-of select="translate(practice/name, $lowercase, $uppercase)"/>*</HL2010AB.4>
  <HL2010AB.5>****</HL2010AB.5>
  <HL2010AB.6><xsl:value-of select="practice/identifier_type"/>*</HL2010AB.6>
  <HL2010AB.7><xsl:value-of select="translate(practice/identifier, $lowercase, $uppercase)"/>~</HL2010AB.7>
  <HL2010AB.8>N3*</HL2010AB.8>
  <HL2010AB.9><xsl:value-of select="translate(practice/print_address, $lowercase, $uppercase)"/>~</HL2010AB.9>
  <HL2010AB.10>N4*</HL2010AB.10>
  <HL2010AB.11><xsl:value-of select="translate(practice/city, $lowercase, $uppercase)"/>*</HL2010AB.11>
  <HL2010AB.12><xsl:value-of select="translate(practice/state, $lowercase, $uppercase)"/>*</HL2010AB.12>
  <HL2010AB.13><xsl:call-template name="str-pad-right">
      <xsl:with-param name="input" select="practice/zip"/>
      <xsl:with-param name="length" select="'5'"/>
    </xsl:call-template>~</HL2010AB.13>
  <HL2010AB.14>REF*</HL2010AB.14>
  <HL2010AB.15>1C*</HL2010AB.15>
  <HL2010AB.16><xsl:value-of select="translate(treating_facility/identifier, $lowercase, $uppercase)"/>~</HL2010AB.16>

  <xsl:for-each select="HL2">
    <HL2000B.1>HL*</HL2000B.1>
    <HL2000B.2><xsl:value-of select="hlCount2"/>*</HL2000B.2>
    <HL2000B.3><xsl:value-of select="hlCount"/>*</HL2000B.3>
    <HL2000B.4>22*</HL2000B.4>
    <HL2000B.5>0~</HL2000B.5>
    <HL2000B.6>SBR*</HL2000B.6>
    <HL2000B.7><xsl:value-of select="translate(payer/responsibility, $lowercase, $uppercase)"/>*</HL2000B.7>
    <HL2000B.8><xsl:value-of select="translate(subscriber/relationship_code, $lowercase, $uppercase)"/>*</HL2000B.8>
    <HL2000B.9><xsl:value-of select="translate(subscriber/group_number, $lowercase, $uppercase)"/>*</HL2000B.9>
    <HL2000B.10><xsl:value-of select="translate(subscriber/group_name, $lowercase, $uppercase)"/>*</HL2000B.10>
    <HL2000B.11>****</HL2000B.11>
    <HL2000B.12>MB~</HL2000B.12>
    <xsl:if test="subscriber/relationship = 'self'">
    <HL2000B.13>PAT*</HL2000B.13>
    <HL2000B.14>****</HL2000B.14>
    <HL2000B.15>D8*</HL2000B.15>
    <HL2000B.16><xsl:value-of select="patient/date_of_death"/>*</HL2000B.16>
    <HL2000B.17>01*</HL2000B.17>
    <HL2000B.18><xsl:value-of select="patient/weight"/>~</HL2000B.18>
    </xsl:if>

    <HL2010BA.1>NM1*</HL2010BA.1>
    <HL2010BA.2>IL*</HL2010BA.2>
    <HL2010BA.3>1*</HL2010BA.3>
    <HL2010BA.4><xsl:value-of select="translate(subscriber/last_name, $lowercase, $uppercase)"/>*</HL2010BA.4>
    <HL2010BA.5><xsl:value-of select="translate(subscriber/first_name, $lowercase, $uppercase)"/>*</HL2010BA.5>
    <HL2010BA.6><xsl:value-of select="translate(subscriber/middle_name, $lowercase, $uppercase)"/>**</HL2010BA.6>
    <HL2010BA.7>*</HL2010BA.7>
    <HL2010BA.8>MI*</HL2010BA.8>
    <HL2010BA.9><xsl:value-of select="subscriber/group_number"/>~</HL2010BA.9>
    <HL2010BA.10>N3*</HL2010BA.10>
    <HL2010BA.11><xsl:value-of select="translate(subscriber/print_address, $lowercase, $uppercase)"/>~</HL2010BA.11>
    <HL2010BA.12>N4*</HL2010BA.12>
    <HL2010BA.13><xsl:value-of select="translate(subscriber/city, $lowercase, $uppercase)"/>*</HL2010BA.13>
    <HL2010BA.14><xsl:value-of select="translate(subscriber/state, $lowercase, $uppercase)"/>*</HL2010BA.14>
    <HL2010BA.15><xsl:call-template name="str-pad-right">
      <xsl:with-param name="input" select="subscriber/zip"/>
      <xsl:with-param name="length" select="'5'"/>
    </xsl:call-template>~</HL2010BA.15>
    <HL2010BA.16>DMG*</HL2010BA.16>
    <HL2010BA.17>D8*</HL2010BA.17>
    <HL2010BA.18><xsl:value-of select="subscriber/date_of_birth"/>*</HL2010BA.18>
    <HL2010BA.19><xsl:value-of select="translate(subscriber/gender, $lowercase, $uppercase)"/>~</HL2010BA.19>
    <HL2010BA.20></HL2010BA.20>

    <HL2010BB.1>NM1*</HL2010BB.1>
    <HL2010BB.2>PR*</HL2010BB.2>
    <HL2010BB.3>2*</HL2010BB.3>
    <HL2010BB.4>UNITED HEALTHCARE*</HL2010BB.4>
    <HL2010BB.5>****</HL2010BB.5>
    <HL2010BB.6>PI*</HL2010BB.6>
    <HL2010BB.7>87726~</HL2010BB.7>

    <xsl:if test="responsible_party/last_name != patient/last_name or responsible_party/first_name != patient/first_name">
    <HL2010BC.1>NM1*</HL2010BC.1>
    <HL2010BC.2>QD*</HL2010BC.2>
    <HL2010BC.3>1*</HL2010BC.3>
    <HL2010BC.4><xsl:value-of select="translate(responsible_party/last_name, $lowercase, $uppercase)"/>*</HL2010BC.4>
    <HL2010BC.5><xsl:value-of select="translate(responsible_party/first_name, $lowercase, $uppercase)"/>*</HL2010BC.5>
    <HL2010BC.6><xsl:value-of select="translate(responsible_party/middle_name, $lowercase, $uppercase)"/>~</HL2010BC.6>
    </xsl:if>
    <HL2010BC.7>N3*</HL2010BC.7>
    <HL2010BC.8><xsl:value-of select="translate(responsible_party/print_address, $lowercase, $uppercase)"/>~</HL2010BC.8>
    <HL2010BC.9>N4*</HL2010BC.9>
    <HL2010BC.10><xsl:value-of select="translate(responsible_party/city, $lowercase, $uppercase)"/>*</HL2010BC.10>
    <HL2010BC.11><xsl:value-of select="translate(responsible_party/state, $lowercase, $uppercase)"/>*</HL2010BC.11>
    <HL2010BC.12><xsl:call-template name="str-pad-right">
      <xsl:with-param name="input" select="responsible_party/zip"/>
      <xsl:with-param name="length" select="'5'"/>
    </xsl:call-template>~</HL2010BC.12>

    <xsl:for-each select="CLM">
      <CLM2300.1>CLM*</CLM2300.1>
      <CLM2300.2><xsl:value-of select="translate(claim/claim_id, $lowercase, $uppercase)"/>*</CLM2300.2>
      <CLM2300.3><xsl:value-of select="claim_line/amount"/>***</CLM2300.3>
      <CLM2300.4><xsl:value-of select="translate(treating_facility/facility_code, $lowercase, $uppercase)"/>::1*</CLM2300.4>
      <CLM2300.5><xsl:value-of select="translate(provider/signature_on_file, $lowercase, $uppercase)"/>*</CLM2300.5>
      <CLM2300.6><xsl:value-of select="translate(provider/accepts_assignment, $lowercase, $uppercase)"/>*</CLM2300.6>
      <CLM2300.7>Y*</CLM2300.7>
      <CLM2300.8>Y*</CLM2300.8>
      <CLM2300.9>C~</CLM2300.9>
      <CLM2300.10>DTP*</CLM2300.10>
      <CLM2300.11>454*</CLM2300.11>
      <CLM2300.12>D8*</CLM2300.12>
      <CLM2300.13><xsl:value-of select="patient/date_of_initial_treatment"/>~</CLM2300.13>

      <!-- X12 Optional Dates Start -->
      <xsl:if test="patient/date_of_last_visit">
      <CLM2300.14.1>DTP*374*D8*<xsl:value-of select="patient/date_of_last_visit"/>~</CLM2300.14.1>
      </xsl:if>
      <xsl:if test="patient/date_of_onset">
      <CLM2300.14.2>DTP*431*D8*<xsl:value-of select="patient/date_of_onset"/>~</CLM2300.14.2>
      </xsl:if>
      <xsl:if test="patient/date_of_last_visit">
      <CLM2300.14.3>DTP*453*D8*<xsl:value-of select="claim/date_of_acute_manifestation"/>~</CLM2300.14.3>
      </xsl:if>
      <xsl:if test="patient/date_of_last_visit">
      <CLM2300.14.4>DTP*438*D8*<xsl:value-of select="claim/date_of_similar_onset"/>~</CLM2300.14.4>
      </xsl:if>
      <xsl:if test="patient/date_of_accident">
      <CLM2300.14.5>DTP*439*D8*<xsl:value-of select="claim/date_of_accident"/>~</CLM2300.14.5>
      </xsl:if>
      <xsl:if test="patient/date_of_last_menstrual_period">
      <CLM2300.14.6>DTP*484*D8*<xsl:value-of select="patient/date_of_last_menstrual_period"/>~</CLM2300.14.6>
      </xsl:if>
      <xsl:if test="patient/date_of_last_xray">
      <CLM2300.14.7>DTP*455*D8*<xsl:value-of select="patient/date_of_last_xray"/>~</CLM2300.14.7>
      </xsl:if>
      <xsl:if test="patient/date_of_hearing_vision_prescription">
      <CLM2300.14.8>DTP*471*D8*<xsl:value-of select="patient/date_of_hearing_vision_prescription"/>~</CLM2300.14.8>
      </xsl:if>
      <xsl:if test="patient/date_of_disability_begin">
      <CLM2300.14.9>DTP*360*D8*<xsl:value-of select="patient/date_of_disability_begin"/>~</CLM2300.14.9>
      </xsl:if>
      <xsl:if test="patient/date_of_disability_end">
      <CLM2300.14.10>DTP*361*D8*<xsl:value-of select="patient/date_of_disability_end"/>~</CLM2300.14.10>
      </xsl:if>
      <xsl:if test="patient/date_of_last_work">
      <CLM2300.14.11>DTP*297*D8*<xsl:value-of select="patient/date_of_last_work"/>~</CLM2300.14.11>
      </xsl:if>
      <xsl:if test="patient/date_auth_return_to_work">
      <CLM2300.14.12>DTP*296*D8*<xsl:value-of select="patient/date_auth_return_to_work"/>~</CLM2300.14.12>
      </xsl:if>
      <xsl:if test="patient/date_of_admission">
      <CLM2300.14.13>DTP*435*D8*<xsl:value-of select="patient/date_of_admission"/>~</CLM2300.14.13>
      </xsl:if>
      <xsl:if test="patient/date_of_discharge">
      <CLM2300.14.14>DTP*096*D8*<xsl:value-of select="patient/date_of_discharge"/>~</CLM2300.14.14>
      </xsl:if>
      <xsl:if test="patient/date_of_assumed_care">
      <CLM2300.14.15.1>DTP*</CLM2300.14.15.1>
      <CLM2300.14.15.2>090*</CLM2300.14.15.2>
      <CLM2300.14.15.3>D8*</CLM2300.14.15.3>
      <CLM2300.14.15.4><xsl:value-of select="patient/date_of_assumed_care"/>~</CLM2300.14.15.4>
      </xsl:if>
      <!-- X12 Optional Dates End -->

      <xsl:if test="billing_facility/clia_number != ''">
      <CLM2300.15.1>REF*</CLM2300.15.1>
      <CLM2300.15.2>X4*</CLM2300.15.2>
      <CLM2300.15.3><xsl:value-of select="billing_facility/clia_number"/>~</CLM2300.15.3>
      </xsl:if>

      <xsl:if test="subscriber/contract_type_code != ''">
      <CLM2300.16.1>CN1*</CLM2300.16.1>
      <CLM2300.16.3><xsl:value-of select="subscriber/contract_type_code"/>*</CLM2300.16.3>
      <CLM2300.16.3><xsl:value-of select="subscriber/contract_amount"/>*</CLM2300.16.3>
      <CLM2300.16.3><xsl:value-of select="subscriber/contract_percent"/>*</CLM2300.16.3>
      <CLM2300.16.3><xsl:value-of select="subscriber/contract_code"/>*</CLM2300.16.3>
      <CLM2300.16.3><xsl:value-of select="subscriber/contract_discount_percent"/>*</CLM2300.16.3>
      <CLM2300.16.3><xsl:value-of select="subscriber/contract_version"/>*</CLM2300.16.3>
      <CLM2300.16.2>~</CLM2300.16.2>
      </xsl:if>

      <xsl:if test="clearing_house/credit_max_amount != ''">
      <CLM2300.17.1>AMT*</CLM2300.17.1>
      <CLM2300.17.2>MA*</CLM2300.17.2>
      <CLM2300.17.3><xsl:value-of select="clearing_house/credit_max_amount"/>~</CLM2300.17.3>
      </xsl:if>

      <xsl:if test="patient/comment != ''">
      <CLM2300.18.1>NTE*</CLM2300.18.1>
      <CLM2300.18.2><xsl:value-of select="translate(patient/comment_type, $lowercase, $uppercase)"/>*</CLM2300.18.2>
      <CLM2300.18.3><xsl:value-of select="translate(patient/comment, $lowercase, $uppercase)"/>~</CLM2300.18.3>
      </xsl:if>

      <CLM2300.19.1>HI*</CLM2300.19.1>
      <CLM2300.19.2>BK:<xsl:value-of select="claim_line/diagnosis1"/></CLM2300.19.2>
      <xsl:if test="claim_line/diagnosis2 != ''">
      <CLM2300.19.3>*BF:<xsl:value-of select="claim_line/diagnosis2"/></CLM2300.19.3>
      </xsl:if>
      <xsl:if test="claim_line/diagnosis3 != ''">
      <CLM2300.19.4>*BF:<xsl:value-of select="claim_line/diagnosis3"/></CLM2300.19.4>
      </xsl:if>
      <xsl:if test="claim_line/diagnosis4 != ''">
      <CLM2300.19.5>*BF:<xsl:value-of select="claim_line/diagnosis4"/></CLM2300.19.5>
      </xsl:if>
      <xsl:if test="claim_line/diagnosis5 != ''">
      <CLM2300.19.6>*BF:<xsl:value-of select="claim_line/diagnosis5"/></CLM2300.19.6>
      </xsl:if>
      <xsl:if test="claim_line/diagnosis6 != ''">
      <CLM2300.19.7>*BF:<xsl:value-of select="claim_line/diagnosis6"/></CLM2300.19.7>
      </xsl:if>
      <xsl:if test="claim_line/diagnosis7 != ''">
      <CLM2300.19.8>*BF:<xsl:value-of select="claim_line/diagnosis7"/></CLM2300.19.8>
      </xsl:if>
      <xsl:if test="claim_line/diagnosis8 != ''">
      <CLM2300.19.9>*BF:<xsl:value-of select="claim_line/diagnosis8"/></CLM2300.19.9>
      </xsl:if>
      <CLM2300.19.10>~</CLM2300.19.10>

      <xsl:if test="clearing_house/repricing_method != ''">
      <CLM2300.20.1>HCP*</CLM2300.20.1>
      <CLM2300.20.2><xsl:value-of select="clearing_house/repricing_method"/>*</CLM2300.20.2>
      <CLM2300.20.3><xsl:value-of select="clearing_house/allowed_amount"/>*</CLM2300.20.3>
      <CLM2300.20.4><xsl:value-of select="clearing_house/savings_amount"/>*</CLM2300.20.4>
      <CLM2300.20.5><xsl:value-of select="clearing_house/identifier"/>*</CLM2300.20.5>
      <CLM2300.20.6><xsl:value-of select="clearing_house/rate"/>*</CLM2300.20.6>
      <CLM2300.20.7><xsl:value-of select="clearing_house/apg_code"/>*</CLM2300.20.7>
      <CLM2300.20.8><xsl:value-of select="clearing_house/apg_amount"/>******</CLM2300.20.8>
      <CLM2300.20.9><xsl:value-of select="clearing_house/reject_code"/>*</CLM2300.20.9>
      <CLM2300.20.10><xsl:value-of select="clearing_house/compliance_code"/>*</CLM2300.20.10>
      <CLM2300.20.11><xsl:value-of select="clearing_house/exception_code"/>~</CLM2300.20.11>
      </xsl:if>


      <xsl:if test="referring_provider/last_name != ''">
      <CLM2310A.1.1>NM1*</CLM2310A.1.1>
      <CLM2310A.1.2><xsl:value-of select="translate(referring_provider/referral_type, $lowercase, $uppercase)"/>*</CLM2310A.1.2>
      <CLM2310A.1.3>1*</CLM2310A.1.3>
      <CLM2310A.1.4><xsl:value-of select="translate(referring_provider/last_name, $lowercase, $uppercase)"/>*</CLM2310A.1.4>
      <CLM2310A.1.5><xsl:value-of select="translate(referring_provider/first_name, $lowercase, $uppercase)"/>*</CLM2310A.1.5>
      <CLM2310A.1.6>***</CLM2310A.1.6>
      <CLM2310A.1.7><xsl:value-of select="translate(referring_provider/identifier_type, $lowercase, $uppercase)"/>*</CLM2310A.1.7>
      <CLM2310A.1.8><xsl:value-of select="translate(referring_provider/identifier, $lowercase, $uppercase)"/>~</CLM2310A.1.8>
      </xsl:if>

      <xsl:if test="referring_provider/taxonomy_code != ''">
      <CLM2310A.2.1>PRV*</CLM2310A.2.1>
      <CLM2310A.2.2>RF*</CLM2310A.2.2>
      <CLM2310A.2.3>ZZ*</CLM2310A.2.3>
      <CLM2310A.2.4><xsl:value-of select="translate(referring_provider/taxonomy_code, $lowercase, $uppercase)"/>~</CLM2310A.2.4>
      </xsl:if>

      <xsl:if test="provider/last_name != ''">
      <CLM2310B.1>NM1*</CLM2310B.1>
      <CLM2310B.2>82*</CLM2310B.2>
      <CLM2310B.3>1*</CLM2310B.3>
      <CLM2310B.4><xsl:value-of select="translate(provider/last_name, $lowercase, $uppercase)"/>*</CLM2310B.4>
      <CLM2310B.5><xsl:value-of select="translate(provider/first_name, $lowercase, $uppercase)"/>*</CLM2310B.5>
      <CLM2310B.6>***</CLM2310B.6>
      <CLM2310B.7><xsl:value-of select="translate(provider/identifier_type, $lowercase, $uppercase)"/>*</CLM2310B.7>
      <CLM2310B.8><xsl:value-of select="translate(provider/identifier, $lowercase, $uppercase)"/>~</CLM2310B.8>
      <CLM2310B.9>REF*</CLM2310B.9>
      <CLM2310B.10>1C*</CLM2310B.10>
      <CLM2310B.11><xsl:value-of select="translate(provider/identifier_2, $lowercase, $uppercase)"/>~</CLM2310B.11>
      </xsl:if>

      <CLM2310D.1>NM1*</CLM2310D.1>
      <CLM2310D.2>FA*</CLM2310D.2>
      <CLM2310D.3>2*</CLM2310D.3>
      <CLM2310D.4><xsl:value-of select="translate(treating_facility/name, $lowercase, $uppercase)"/>~</CLM2310D.4>
      <CLM2310D.5>N3*</CLM2310D.5>
      <CLM2310D.6><xsl:value-of select="translate(treating_facility/print_address, $lowercase, $uppercase)"/>~</CLM2310D.6>
      <CLM2310D.7>N4*</CLM2310D.7>
      <CLM2310D.8><xsl:value-of select="translate(treating_facility/city, $lowercase, $uppercase)"/>*</CLM2310D.8>
      <CLM2310D.9><xsl:value-of select="translate(treating_facility/state, $lowercase, $uppercase)"/>*</CLM2310D.9>
      <CLM2310D.10><xsl:call-template name="str-pad-right">
        <xsl:with-param name="input" select="treating_facility/zip"/>
        <xsl:with-param name="length" select="'5'"/>
      </xsl:call-template>~</CLM2310D.10>

      <xsl:if test="supervising_provider/last_name != ''">
      <CLM2310E.1>NM1*</CLM2310E.1>
      <CLM2310E.2>DQ*</CLM2310E.2>
      <CLM2310E.3>1*</CLM2310E.3>
      <CLM2310E.4><xsl:value-of select="translate(supervising_provider/last_name, $lowercase, $uppercase)"/>*</CLM2310E.4>
      <CLM2310E.5><xsl:value-of select="translate(supervising_provider/first_name, $lowercase, $uppercase)"/>*</CLM2310E.5>
      <CLM2310E.6>***</CLM2310E.6>
      <CLM2310E.7><xsl:value-of select="translate(supervising_provider/identifier_type, $lowercase, $uppercase)"/>*</CLM2310E.7>
      <CLM2310E.8><xsl:value-of select="translate(supervising_provider/identifier, $lowercase, $uppercase)"/>~</CLM2310E.8>
      </xsl:if>

      <xsl:if test="payer2">
      <CLM2320.1>SBR*S*18***MI****16~</CLM2320.1>
      <CLM2320.2>DMG*</CLM2320.2>
      <CLM2320.3>D8*</CLM2320.3>
      <CLM2320.4><xsl:value-of select="translate(subscriber/date_of_birth, $lowercase, $uppercase)"/>*</CLM2320.4>
      <CLM2320.5><xsl:value-of select="translate(subscriber/gender, $lowercase, $uppercase)"/>~</CLM2320.5>
      <CLM2320.6>OI***Y*B**Y~</CLM2320.6>
      <CLM2320.7>NM1*</CLM2320.7>
      <CLM2320.8>IL*</CLM2320.8>
      <CLM2320.9>1*</CLM2320.9>
      <CLM2320.10><xsl:value-of select="translate(subscriber/last_name, $lowercase, $uppercase)"/>*</CLM2320.10>
      <CLM2320.11><xsl:value-of select="translate(subscriber/first_name, $lowercase, $uppercase)"/>*</CLM2320.11>
      <CLM2320.12><xsl:value-of select="translate(subscriber/middle_name, $lowercase, $uppercase)"/>**</CLM2320.12>
      <CLM2320.13>*</CLM2320.13>
      <CLM2320.14>MI*</CLM2320.14>
      <CLM2320.15><xsl:value-of select="translate(subscriber/id, $lowercase, $uppercase)"/>~</CLM2320.15>
      <CLM2320.16>N3*</CLM2320.16>
      <CLM2320.17><xsl:value-of select="translate(subscriber/print_address, $lowercase, $uppercase)"/>~</CLM2320.17>
      <CLM2320.18>N4*</CLM2320.18>
      <CLM2320.19><xsl:value-of select="translate(subscriber/city, $lowercase, $uppercase)"/>*</CLM2320.19>
      <CLM2320.20><xsl:value-of select="translate(subscriber/state, $lowercase, $uppercase)"/>*</CLM2320.20>
      <CLM2320.21><xsl:call-template name="str-pad-right">
        <xsl:with-param name="input" select="subscriber/zip"/>
        <xsl:with-param name="length" select="'5'"/>
      </xsl:call-template>~</CLM2320.21>

      <CLM2330B.1>NM1*</CLM2330B.1>
      <CLM2330B.2>PR*</CLM2330B.2>
      <CLM2330B.3>2*</CLM2330B.3>
      <CLM2330B.4><xsl:value-of select="translate(payer2/name, $lowercase, $uppercase)"/>*</CLM2330B.4>
      <CLM2330B.5>****</CLM2330B.5>
      <CLM2330B.6>PI*</CLM2330B.6>
      <CLM2330B.7><xsl:value-of select="translate(payer2/id, $lowercase, $uppercase)"/>~</CLM2330B.7>
      </xsl:if>

      <CLM2400.1>LX*</CLM2400.1>
      <CLM2400.2>1~</CLM2400.2>
      <CLM2400.3>SV1*</CLM2400.3>
      <CLM2400.4>HC:<xsl:value-of select="translate(claim_line/procedure, $lowercase, $uppercase)"/></CLM2400.4>
      <xsl:if test="claim_line/modifier1 != ''">
      <CLM2400.5.1>:<xsl:value-of select="translate(claim_line/modifier1, $lowercase, $uppercase)"/></CLM2400.5.1>
      </xsl:if>
      <xsl:if test="claim_line/modifier2 != ''">
      <CLM2400.5.2>:<xsl:value-of select="translate(claim_line/modifier2, $lowercase, $uppercase)"/></CLM2400.5.2>
      </xsl:if>
      <xsl:if test="claim_line/modifier3 != ''">
      <CLM2400.5.3>:<xsl:value-of select="translate(claim_line/modifier3, $lowercase, $uppercase)"/></CLM2400.5.3>
      </xsl:if>
      <xsl:if test="claim_line/modifier4 != ''">
      <CLM2400.5.4>:<xsl:value-of select="translate(claim_line/modifier4, $lowercase, $uppercase)"/></CLM2400.5.4>
      </xsl:if>
      <CLM2400.6>*</CLM2400.6>
      <CLM2400.7><xsl:choose>
          <xsl:when test="claim_line/amount = '0.00'">0</xsl:when>
          <xsl:otherwise><xsl:value-of select="claim_line/amount"/></xsl:otherwise>
        </xsl:choose>*</CLM2400.7>
      <CLM2400.8>UN*</CLM2400.8>
      <CLM2400.9><xsl:value-of select="claim_line/units"/>*</CLM2400.9>
      <CLM2400.10><xsl:value-of select="treating_facility/facility_code"/>**</CLM2400.10>
      <CLM2400.11>1<xsl:if test="claim_line/diagnosis2 != ''">:2</xsl:if>
        <xsl:if test="claim_line/diagnosis3 != ''">:3</xsl:if>
        <xsl:if test="claim_line/diagnosis4 != ''">:4</xsl:if>
        <xsl:if test="claim_line/diagnosis5 != ''">:5</xsl:if>
        <xsl:if test="claim_line/diagnosis6 != ''">:6</xsl:if>
        <xsl:if test="claim_line/diagnosis7 != ''">:7</xsl:if>
        <xsl:if test="claim_line/diagnosis8 != ''">:8</xsl:if></CLM2400.11>
      <CLM2400.12>~</CLM2400.12>
      <CLM2400.13>DTP*</CLM2400.13>
      <CLM2400.14>472*</CLM2400.14>
      <CLM2400.15>D8*</CLM2400.15>
      <CLM2400.16><xsl:value-of select="claim_line/date_of_treatment"/>~</CLM2400.16>
      <xsl:if test="claim_line/clia_number != ''">
      <CLM2400.17>REF*</CLM2400.17>
      <CLM2400.18>X4*</CLM2400.18>
      <CLM2400.19><xsl:value-of select="translate(claim_line/clia_number, $lowercase, $uppercase)"/>~</CLM2400.19>
      </xsl:if>
    </xsl:for-each>
  </xsl:for-each>
</xsl:template>
<!-- TEMPLATE: HL END -->

<!-- TEMPLATE: ISA START -->
<xsl:template match="data/ISA">
  <ISA.1>ISA*</ISA.1>
  <ISA.2>00*</ISA.2>
  <ISA.3><xsl:call-template name="str-pad-right">
      <xsl:with-param name="input" select="''"/>
      <xsl:with-param name="length" select="'10'"/>
    </xsl:call-template>*</ISA.3>
  <ISA.4>00*</ISA.4>
  <ISA.5><xsl:call-template name="str-pad-right">
      <xsl:with-param name="input" select="''"/>
      <xsl:with-param name="length" select="'10'"/>
    </xsl:call-template>*</ISA.5>
  <ISA.6>ZZ*</ISA.6>
  <ISA.7><xsl:variable name="edi7var">
      <xsl:call-template name="str-pad-right">
        <xsl:with-param name="input" select="practice/sender_id"/>
        <xsl:with-param name="length" select="'15'"/>
      </xsl:call-template>
    </xsl:variable>
    <xsl:value-of select="translate($edi7var, $lowercase, $uppercase)"/>*</ISA.7>
  <ISA.8>ZZ*</ISA.8>
  <ISA.9><xsl:call-template name="str-pad-right">
      <xsl:with-param name="input" select="'87726'"/>
      <xsl:with-param name="length" select="'15'"/>
    </xsl:call-template>*</ISA.9>
  <ISA.10><xsl:value-of select="dateNow"/>*</ISA.10>
  <ISA.11><xsl:value-of select="timeNow"/>*</ISA.11>
  <ISA.12>U*</ISA.12>
  <ISA.13>00401*</ISA.13>
  <ISA.14><xsl:call-template name="str-pad-left">
      <xsl:with-param name="input" select="claim/claim_id"/>
      <xsl:with-param name="string" select="'0'"/>
      <xsl:with-param name="length" select="'9'"/>
    </xsl:call-template>*</ISA.14>
  <ISA.15>0*</ISA.15>
  <ISA.16><xsl:choose>
      <xsl:when test="testing">T</xsl:when>
      <xsl:otherwise>P</xsl:otherwise>
    </xsl:choose>*</ISA.16>
  <ISA.17>:~</ISA.17>
</xsl:template>
<!-- TEMPLATE: ISA END -->

<!-- TEMPLATE: GS START -->
<xsl:template match="data/GS">
  <GS.1>GS*</GS.1>
  <GS.2>HC*</GS.2>
  <GS.3><xsl:value-of select="translate(practice/sender_id, $lowercase, $uppercase)"/>*</GS.3>
  <GS.4>87726*</GS.4>
  <GS.5><xsl:value-of select="dateNow"/>*</GS.5>
  <GS.6><xsl:value-of select="timeNow"/>*</GS.6>
  <GS.7><xsl:call-template name="str-pad-left">
      <xsl:with-param name="input" select="claim/claim_id"/>
      <xsl:with-param name="string" select="'0'"/>
      <xsl:with-param name="length" select="'9'"/>
    </xsl:call-template>*</GS.7>
  <GS.8>X*</GS.8>
  <GS.9><xsl:value-of select="translate(practice/x12_version, $lowercase, $uppercase)"/>~</GS.9>
  <GS.10>ST*</GS.10>
  <GS.11>837*</GS.11>
  <GS.12>000000001~</GS.12>
  <GS.13>BHT*</GS.13>
  <GS.14>0019*</GS.14>
  <GS.15>00*</GS.15>
  <!--<GS.16>000000001*</GS.16>-->
  <GS.16>000508*</GS.16>
  <GS.17><xsl:value-of select="dateNow"/>*</GS.17>
  <GS.18><xsl:value-of select="timeNow"/>*</GS.18>
  <GS.19>CH~</GS.19>
  <GS.20>REF*</GS.20>
  <GS.21>87*</GS.21>
  <GS.22><xsl:value-of select="translate(practice/x12_version, $lowercase, $uppercase)"/>~</GS.22>

  <GS1000A.1>NM1*</GS1000A.1>
  <GS1000A.2>41*</GS1000A.2>
  <GS1000A.3>2*</GS1000A.3>
  <GS1000A.4><xsl:value-of select="translate(practice/name, $lowercase, $uppercase)"/>*</GS1000A.4>
  <GS1000A.5>****</GS1000A.5>
  <GS1000A.6>46*</GS1000A.6>
  <GS1000A.7><xsl:value-of select="translate(practice/sender_id, $lowercase, $uppercase)"/>~</GS1000A.7>
  <GS1000A.8>PER*</GS1000A.8>
  <GS1000A.9>IC*</GS1000A.9>
  <GS1000A.10><xsl:value-of select="translate(billing_contact/name, $lowercase, $uppercase)"/>*</GS1000A.10>
  <GS1000A.11>TE*</GS1000A.11>
  <GS1000A.12><xsl:value-of select="billing_contact/phone_number"/>~</GS1000A.12>

  <GS1000B.1>NM1*</GS1000B.1>
  <GS1000B.2>40*</GS1000B.2>
  <GS1000B.3>2*</GS1000B.3>
  <GS1000B.4>UNITED HEALTHCARE*</GS1000B.4>
  <GS1000B.5>****</GS1000B.5>
  <GS1000B.6><xsl:value-of select="payer/identifier_type"/>*</GS1000B.6>
  <GS1000B.7>87726~</GS1000B.7>
</xsl:template>
<!-- TEMPLATE: GS END -->





<!-- UTILITIES: STRING -->
<xsl:template name="str-remove-left">
  <xsl:param name="input"/><!-- the input string -->
  <xsl:param name="length"/><!-- pad length -->
  <xsl:param name="string" select="' '"/><!-- pad string/s -->
  <xsl:choose>
    <xsl:when test="string-length($input) &lt; $length">
      <xsl:call-template name="str-pad-left">
        <xsl:with-param name="input" select="concat($string, $input)"/>
        <xsl:with-param name="length" select="$length"/>
        <xsl:with-param name="string" select="$string"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="substring($input, string-length($input) - $length + 1)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>
<xsl:template name="str-pad-left">
  <xsl:param name="input"/><!-- the input string -->
  <xsl:param name="length"/><!-- pad length -->
  <xsl:param name="string" select="' '"/><!-- pad string/s -->
  <xsl:choose>
    <xsl:when test="string-length($input) &lt; $length">
      <xsl:call-template name="str-pad-left">
        <xsl:with-param name="input" select="concat($string, $input)"/>
        <xsl:with-param name="length" select="$length"/>
        <xsl:with-param name="string" select="$string"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="substring($input, string-length($input) - $length + 1)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="str-pad-right">
  <xsl:param name="input"/><!-- the input string -->
  <xsl:param name="length"/><!-- pad length -->
  <xsl:param name="string" select="' '"/><!-- pad string/s -->
  <xsl:choose>
    <xsl:when test="string-length($input) &lt; $length">
      <xsl:call-template name="str-pad-right">
        <xsl:with-param name="input" select="concat($input, $string)"/>
        <xsl:with-param name="length" select="$length"/>
        <xsl:with-param name="string" select="$string"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="substring($input, 1, $length)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="str-pad-both">
  <xsl:param name="input"/><!-- the input string -->
  <xsl:param name="length"/><!-- pad length -->
  <xsl:param name="string" select="' '"/><!-- pad string/s -->
  <xsl:choose>
    <xsl:when test="string-length($input) &lt; $length">
      <xsl:call-template name="str-pad-both">
        <xsl:with-param name="input" select="concat($string, $input, $string)"/>
        <xsl:with-param name="length" select="$length"/>
        <xsl:with-param name="string" select="$string"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="substring($input, string-length($input) - $length + 1)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>


</xsl:stylesheet>

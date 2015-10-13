<div class="container">
    <div class="row">
        <div class="col-md-12 survey_builder_statistics_filters">
        <form id="survey_template_form" name="survey_template_form" action="$Link($Action)" method="post">
        <select id="ddl_survey_templates" name="survey_template_id">
            <option value="-1">-- select a survey --</option>
            <% loop SurveyBuilderSurveyTemplates($ClassName) %>
                <option value="{$ID}" <% if $Top.IsSurveyTemplateSelected($ID) %>selected<% end_if %> >$QualifiedName</option>
            <% end_loop %>
        </select>
        </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 survey_builder_questions_values_statistics_filters">
            <h2>{$Top.SurveyBuilderLabelSubmitted} &mdash; {$Top.SurveyBuilderSurveyCount} total</h2>
            $DateFilters
            $RenderCurrentFilters
            <% if $RenderCurrentFilters %>
            <a href="$Top.Link($Top.Action)?clear_filters=1">Clear Filters</a>
            <% end_if %>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
        <% if SurveyQuestions2Show %>
            <div class="row">
                <% loop SurveyQuestions2Show %>
                    <% if $ClassName == 'SurveyRadioButtonMatrixTemplateQuestion' %>
                        <% include SangriaPage_StatisticsSurveyRadioButtonMatrixTemplateQuestion ParentPage=$Top, QuestionID=$ID %>
                    <% else %>
                        <% include SangriaPage_StatisticsMultiValueQuestionTemplate ParentPage=$Top %>
                    <% end_if %>
                <% end_loop %>
            </div>
        <% else %>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Warning!</strong> You must set some question to show on Admin CMS.
            </div>
        <% end_if %>
        </div>
    </div>
</div>
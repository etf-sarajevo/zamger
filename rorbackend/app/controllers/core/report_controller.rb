class Core::ReportController < ApplicationController
  #caches_page :course_unit, :cache_path => Proc.new { |c| c.params }, :expires_in => 1.seconds
  # get "/core/Report/courseUnit", :controller => "Core::Report", :action => "course_unit"
  def course_unit
    @controller=self
    @rest_service_url = REST_SERVICE_URL
    @course_unit_id = params[:course_unit_id].to_i
    @academic_year_id = params[:academic_year_id].to_i
    if params[:group_id] != nil
      @group_id = params[:group_id].to_i
    else
      @group_id = 0
    end
    
    if params[:shorten] == 'da'
      @shorten = true
    else
      @shorten = false
    end
    
    if params[:separate_exams] == 'da'
      @separate_exams = true
    else
      @separate_exams = false
    end
    
    if params[:concat_groups] == 'da'
      @concat_groups = true
    else
      @concat_groups = false
    end
     
    url_course_unit = REST_SERVICE_URL + '/core/CourseUnit/' + @course_unit_id.to_s
    data_course_unit = RestClient.get url_course_unit, { :accept => :json }
    deal_exception(data_course_unit)
    @data_course_unit = JSON.parse(data_course_unit)
    
    
    url_academic_year = REST_SERVICE_URL + '/core/AcademicYear/' + @academic_year_id.to_s
    data_academic_year = RestClient.get url_academic_year, { :accept => :json }
    deal_exception(data_academic_year)
    @data_academic_year = JSON.parse(data_academic_year)
    
    
    url_students = REST_SERVICE_URL + '/core/CourseUnit/' + @course_unit_id.to_s + "/getAllStudents?academic_year_id=" + @academic_year_id.to_s
    data_students = RestClient.get url_students, { :accept => :json }
    deal_exception(data_students)
    @students = JSON.parse(data_students)
    @name_surname = {}
    @student_id_numbers = {}
    @students.each do |student|
      @name_surname[student['id'].to_i] = student['name']+" "+student['surname']
      @student_id_numbers[student['id'].to_i] = student['student_id_number']
    end
    @groups = [{'id' => 0, 'name' => ""}]
    if (@concat_groups == false)
      if (@group_id > 0)
        @groups << (Lms::Attendance::Group).find(@group_id)
      else
        @groups = @groups + (Lms::Attendance::Group).from_course_unit(@course_unit_id, @academic_year_id)
        @groups = @groups.sort_by { |g| g['name'].scan(/\D+|\d+/).map { |x| x =~ /\d/ ? x.to_i : x } }
      end
    end
    
    
    
    url_group = REST_SERVICE_URL + '/lms/attendance/Group/fromCourseUnitVirtual?course_unit_id=' + @course_unit_id.to_s + "&academic_year_id=" + @academic_year_id.to_s
    begin
      data_group = RestClient.get url_group, { :accept => :json }
      g = JSON.parse(data_group) 
      @virtual_group_id = g['id']
    rescue
      @virtual_group_id = 0
    end
    @groups[0] = {'id' => @virtual_group_id, 'name' => "[Bez grupe]"}
    @exams = {}
    @exams_number = 0
    @exam_header = ""
    @old_scoring_element = 0
    
    if (@separate_exams == true)
      order = "date"
    else
      order = "scoring_element_id"
    end
    
    url_exams = REST_SERVICE_URL + '/lms/exam/Exam/fromCourse?course_unit_id=' + @course_unit_id.to_s + "&academic_year_id=" + @academic_year_id.to_s + "&order=" + order
    
    begin
      data_exams = RestClient.get url_exams, { :accept => :json }
      @exams = JSON.parse(data_exams)
    rescue
      @exams = []
    end
      
    @scoring_elements_scoring = {}
    @scoring_elements_max = {}
    @scoring_elements_pass = {}
    @scoring_elements_option = {}
    @exams_scoring_elements = {}
    @has_integral = false
    @exam_ids = []
    @scoring_elements = {}
    @exams.each do |exam|
      @exam_ids << exam['id'].to_i
      @exams_scoring_elements[exam['id']] = exam['scoring_element']['id']
      if @separate_exams == true
        @exam_header += "<td align=\"center\">"+exam['scoring_element']['short_gui_name']+"<br/> " + date("d.m.",$r30[1]) + "</td>\n";
        @exams_number += 1
      elsif exam['scoring_element_id'].to_i != @old_scoring_element and exam['scoring_element']['scoring_id'].to_i != 2
        @old_scoring_element = exam['scoring_element']
        @exam_header += "<td align=\"center\">"+exam['scoring_element']['short_gui_name']+"<br/> "
        @exams_number += 1
      elsif exam['scoring_element']['scoring_id'].to_i == 2
        @has_integral = true
      end
      @exam_ids << exam['id'].to_i
      @scoring_elements[exam['id'].to_i] = {:scoring_id => exam['scoring_element']['scoring_id'], :max => exam['scoring_element']['max'], :option => exam['scoring_element']['option']}
      @scoring_elements_scoring[exam['scoring_element']['id'].to_i] = exam['scoring_element']['scoring_id']
      @scoring_elements_max[exam['scoring_element']['id'].to_i] = exam['scoring_element']['max']
      @scoring_elements_pass[exam['scoring_element']['id'].to_i] = exam['scoring_element']['pass']
      @scoring_elements_option[exam['scoring_element']['id'].to_i] = exam['scoring_element']['option']
      
    end
    
    @max_score = 0
    @scoring_elements.each do |id, scoring_element|
      if (scoring_element['scoring_id'].to_i != 2 or (@has_integral == true and @exams_number < 2))
        @max_score += scoring_element['max'].to_f
      end
    end
    
    if (@has_integral == true and @exams_number < 2)
      @exams_number = 2
    end
        
    url_scoring_elements = REST_SERVICE_URL + '/core/ScoringElement/fromCourseUnitExceptExams?course_unit_id=' + @course_unit_id.to_s + "&academic_year_id=" + @academic_year_id.to_s
    data_scoring_elements = RestClient.get url_scoring_elements, { :accept => :json }
    deal_exception(data_scoring_elements)
    @scorings_elements = JSON.parse(data_scoring_elements)
    
    @other_scoring_elements = {}
    @scorings_elements.each do |scoring_element|
      @max_score += scoring_element[:max].to_f
      
      next if @shorten == false and scoring_element['scoring_id'].to_i != 5
      
      @other_scoring_elements[scoring_element['id'].to_i] = scoring_element['short_gui_name']
    end
    
    
    @homework_header1 = @homework_header2 = ""
    
    if (@shorten == false)
      @homeworks_max = {}
      @homeworks_ids = []
      @homeworks_brz = {}
      @homeworks_option = {}
      
      url_scoring_elements_homeworks = REST_SERVICE_URL + '/lms/homework/Homework/fromCourseScoringElement?course_unit_id=' + @course_unit_id.to_s + "&academic_year_id=" + @academic_year_id.to_s
      begin
        data_scoring_elements_homeworks = RestClient.get url_scoring_elements_homeworks, { :accept => :json }
        @scoring_elements_homeworks = JSON.parse(data_scoring_elements_homeworks)
      rescue
        @scoring_elements_homeworks = []
      end
      
      @scoring_elements_homeworks.each do |scoring_element_homework|
        @homeworks_number = 0
        @homeworks_header = ""
        begin
        url_homeworks = REST_SERVICE_URL + '/lms/homework/Homework/fromCourse?course_unit_id=' + @course_unit_id.to_s + "&academic_year_id=" + @academic_year_id.to_s
        data_homeworks = RestClient.get url_homeworks, { :accept => :json }
          homeworks = JSON.parse(data_homeworks)
        rescue
          homeworks = []
        end
        homeworks.each do |homework|
          
          @homeworks_header += "<td width=\"60\">"+homework['name'].to_s+"</td>\n"
          @homeworks_ids << homework['id']
          @homeworks_brz[homework['id'].to_i] = homework['nr_assignments']
          @homeworks_max[homework['id'].to_i] = homework['max']
          @homeworks_option[homework['id'].to_i] = homework['score']
          @homeworks_number += 1
        end
        
        if @homeworks_number > 0
          @homework_header1 += "<td align=\"center\" colspan=\""+@homeworks_number.to_s+"\">"+scoring_element_homework['gui_name']+"</td>\n"
          @homework_header2 += @homeworks_header
        else
          @homework_header1 += "<td align=\"center\" rowspan=\"2\">"+scoring_element_homework['gui_name']+"</td>\n"
        end
      end
      
    end
    
    
    if (@shorten == false)
      @homeworks = {}
      if (@group_id != 0)
        url_assignments = REST_SERVICE_URL + '/lms/homework/Assignment/fromGroup?group_id=' + @group_id.to_s
        data_assignments = RestClient.get url_assignments, { :accept => :json }
        deal_exception(data_assignments)
        @assignments = JSON.parse(data_assignments)
      else
        url_assignments = REST_SERVICE_URL + '/lms/homework/Assignment/fromCourseUnit?course_unit_id=' + @course_unit_id.to_s + "&academic_year_id=" + @academic_year_id.to_s
        data_assignments = RestClient.get url_assignments, { :accept => :json }
        deal_exception(data_assignments)
        @assignments = JSON.parse(data_assignments)
      end
      @assignments.each do |assignment|
        if (assignment['status'] != 1 and assignment['status'] != 4)
          score = assignment['score'] + 1
        else
          score = -1
        end
        
        @homeworks[[assignment['homework_id'], assignment['assign_no'], assignment['student_id']]] = score
      end
      
    end

    @names_option = true
    if (!self.user_teacher and !self.user_student_service and !self.user_site_admin)
        @names_option = false
    end
    
    respond_to do |format|
      format.html
    end
  end
  
  # get "/core/Report/courseUnitWORest", :controller => "Core::Report", :action => "course_unit_wo_rest"
  def course_unit_wo_rest
    @controller=self
    @rest_service_url = REST_SERVICE_URL
    @course_unit_id = params[:course_unit_id].to_i
    @academic_year_id = params[:academic_year_id].to_i
    if params[:group_id] != nil
      @group_id = params[:group_id].to_i
    else
      @group_id = 0
    end
    
    if params[:shorten] == 'da'
      @shorten = true
    else
      @shorten = false
    end
    
    if params[:separate_exams] == 'da'
      @separate_exams = true
    else
      @separate_exams = false
    end
    
    if params[:concat_groups] == 'da'
      @concat_groups = true
    else
      @concat_groups = false
    end
    
    @data_course_unit = (Core::CourseUnit).find(@course_unit_id)

    @data_academic_year = (Core::AcademicYear).find(@academic_year_id)
    
    @students = (Core::CourseUnit).get_all_students(@course_unit_id, @academic_year_id)
    @name_surname = {}
    @student_id_numbers = {}
    @students.each do |student|
      @name_surname[student['id'].to_i] = student['name']+" "+student['surname']
      @student_id_numbers[student['id'].to_i] = student['student_id_number']
    end
    @groups = [{'id' => 0, 'name' => ""}]
    if (@concat_groups == false)
      if (@group_id > 0)
        @groups << (Lms::Attendance::Group).find(@group_id)
      else
        @groups = @groups + (Lms::Attendance::Group).from_course_unit(@course_unit_id, @academic_year_id)
        @groups = @groups.sort_by { |g| g['name'].scan(/\D+|\d+/).map { |x| x =~ /\d/ ? x.to_i : x } }
      end
    end
 
    begin
      data_group = RestClient.get url_group, { :accept => :json }
      g = (Lms::Attendance::Group).from_course_unit_virtual_id(@course_unit_id, @academic_year_id)
      @virtual_group_id = g['id']
    rescue
      @virtual_group_id = 0
    end
    @groups[0] = {'id' => @virtual_group_id, 'name' => "[Bez grupe]"}
    @exams = {}
    @exams_number = 0
    @exam_header = ""
    @old_scoring_element = 0
    
    if (@separate_exams == true)
      order = "date"
    else
      order = "scoring_element_id"
    end
    url_exams = REST_SERVICE_URL + '/lms/exam/Exam/fromCourse?course_unit_id=' + @course_unit_id.to_s + "&academic_year_id=" + @academic_year_id.to_s + "&order=" + order
    data_exams = RestClient.get url_exams, { :accept => :json }
    deal_exception(data_exams)
    @exams = JSON.parse(data_exams)
    
    @scoring_elements_scoring = {}
    @scoring_elements_max = {}
    @scoring_elements_pass = {}
    @scoring_elements_option = {}
    @exams_scoring_elements = {}
    @has_integral = false
    @exam_ids = []
    @scoring_elements = {}
    @exams.each do |exam|
      @exam_ids << exam['id'].to_i
      @exams_scoring_elements[exam['id']] = exam['scoring_element']['id']
      if @separate_exams == true
        @exam_header += "<td align=\"center\">"+exam['scoring_element']['short_gui_name']+"<br/> " + date("d.m.",$r30[1]) + "</td>\n";
        @exams_number += 1
      elsif exam['scoring_element_id'].to_i != @old_scoring_element and exam['scoring_element']['scoring_id'].to_i != 2
        @old_scoring_element = exam['scoring_element']
        @exam_header += "<td align=\"center\">"+exam['scoring_element']['short_gui_name']+"<br/> "
        @exams_number += 1
      elsif exam['scoring_element']['scoring_id'].to_i == 2
        @has_integral = true
      end
      @exam_ids << exam['id'].to_i
      @scoring_elements[exam['id'].to_i] = {:scoring_id => exam['scoring_element']['scoring_id'], :max => exam['scoring_element']['max'], :option => exam['scoring_element']['option']}
      @scoring_elements_scoring[exam['scoring_element']['id'].to_i] = exam['scoring_element']['scoring_id']
      @scoring_elements_max[exam['scoring_element']['id'].to_i] = exam['scoring_element']['max']
      @scoring_elements_pass[exam['scoring_element']['id'].to_i] = exam['scoring_element']['pass']
      @scoring_elements_option[exam['scoring_element']['id'].to_i] = exam['scoring_element']['option']
      
    end
    
    @max_score = 0
    @scoring_elements.each do |id, scoring_element|
      if (scoring_element['scoring_id'].to_i != 2 or (@has_integral == true and @exams_number < 2))
        @max_score += scoring_element['max'].to_f
      end
    end
    
    if (@has_integral == true and @exams_number < 2)
      @exams_number = 2
    end
        
    @scorings_elements = (Core::ScoringElement).from_course_unit_except_exams(@course_unit_id, @academic_year_id)
    
    @other_scoring_elements = {}
    @scorings_elements.each do |scoring_element|
      @max_score += scoring_element[:max].to_f
      
      next if @shorten == false and scoring_element['scoring_id'].to_i != 5
      
      @other_scoring_elements[scoring_element['id'].to_i] = scoring_element['short_gui_name']
    end
    
    
    @homework_header1 = @homework_header2 = ""
    
    if (@shorten == false)
      @homeworks_max = {}
      @homeworks_ids = []
      @homeworks_brz = {}
      @homeworks_option = {}
      
      @academic_year_id.to_s
      begin
        @scoring_elements_homeworks = (Lms::Homework::Homework).from_course_scoring_element(@course_unit_id, @academic_year_id)
      rescue
        @scoring_elements_homeworks = []
      end
      
      @scoring_elements_homeworks.each do |scoring_element_homework|
        @homeworks_number = 0
        @homeworks_header = ""
        begin
        homeworks = (Lms::Homework::Homework).from_course(@course_unit_id, @academic_year_id)
        rescue
          homeworks = []
        end
        homeworks.each do |homework|
          
          @homeworks_header += "<td width=\"60\">"+homework['name'].to_s+"</td>\n"
          @homeworks_ids << homework['id']
          @homeworks_brz[homework['id'].to_i] = homework['nr_assignments']
          @homeworks_max[homework['id'].to_i] = homework['max']
          @homeworks_option[homework['id'].to_i] = homework['score']
          @homeworks_number += 1
        end
        
        if @homeworks_number > 0
          @homework_header1 += "<td align=\"center\" colspan=\""+@homeworks_number.to_s+"\">"+scoring_element_homework['gui_name']+"</td>\n"
          @homework_header2 += @homeworks_header
        else
          @homework_header1 += "<td align=\"center\" rowspan=\"2\">"+scoring_element_homework['gui_name']+"</td>\n"
        end
      end
      
    end
    
    
    if (@shorten == false)
      @homeworks = {}
      if (@group_id != 0)
        @assignments = (Lms::Homework::Assignment).from_group(@group_id)
      else
        @assignments = (Lms::Homework::Assignment).from_course_unit(@course_unit_id, @academic_year_id)
      end
      @assignments.each do |assignment|
        if (assignment['status'] != 1 and assignment['status'] != 4)
          score = assignment['score'] + 1
        else
          score = -1
        end
        
        @homeworks[[assignment['homework_id'], assignment['assign_no'], assignment['student_id']]] = score
      end
      
    end

    @names_option = true
    if (!self.user_teacher and !self.user_student_service and !self.user_site_admin)
        @names_option = false
    end
    
    respond_to do |format|
      format.html
    end
  end
  
  def deal_exception(response)
    if response.code != 200
      throw ActiveRecord::RecordNotFound
    end
  end
  

end

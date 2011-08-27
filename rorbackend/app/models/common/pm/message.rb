class Common::Pm::Message < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'poruka'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :type, :tip
  # alias_attribute :scope, :opseg
  # alias_attribute :to_id, :primalac
  # alias_attribute :from_id, :posiljalac
  # alias_attribute :time, :vrijeme
  # alias_attribute :ref_id, :ref
  # alias_attribute :subject, :naslov
  # alias_attribute :text, :tekst

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'poruka'
  # ID = TABLE_NAME + '.' + 'id'
  # TYPE = TABLE_NAME + '.' + 'tip'
  # SCOPE = TABLE_NAME + '.' + 'opseg'
  # TO_ID = TABLE_NAME + '.' + 'primalac'
  # FROM_ID = TABLE_NAME + '.' + 'posiljalac'
  # TIME = TABLE_NAME + '.' + 'vrijeme'
  # REF_ID = TABLE_NAME + '.' + 'ref'
  # SUBJECT = TABLE_NAME + '.' + 'naslov'
  # TEXT = TABLE_NAME + '.' + 'tekst'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'common_pm_messages'
  ID = TABLE_NAME + '.' + 'id'
  TYPE = TABLE_NAME + '.' + 'type'
  SCOPE = TABLE_NAME + '.' + 'scope'
  TO_ID = TABLE_NAME + '.' + 'to_id'
  FROM_ID = TABLE_NAME + '.' + 'from_id'
  TIME = TABLE_NAME + '.' + 'time'
  REF_ID = TABLE_NAME + '.' + 'ref_id'
  SUBJECT = TABLE_NAME + '.' + 'subject'
  TEXT = TABLE_NAME + '.' + 'text'

  ALL_COLUMNS = [ID, TYPE, SCOPE, TO_ID, FROM_ID, TIME, REF_ID, SUBJECT, TEXT]
  

  SCOPES = {:all_users => 0, :all_students => 1, :all_teachers => 2, :programme_students => 3, :academic_year_students => 4, :course_unit_students => 5, :group_students => 6, :personal_message => 7}
  TYPES = {:announcement => 1, :personal_message => 2}
  
  belongs_to :to, :class_name => "Core::Person"
  belongs_to :from, :class_name => "Core::Person"
  belongs_to :message, :foreign_key => "ref_id"
  validates_presence_of :type, :scope, :to_id, :from_id, :time, :subject, :text
  
  
  def self.for_person(id, person_id, is_student)
    result = {}
    
    person = (Core::Person).find(person_id)
    current_year = (Core::AcademicYear).current_year
    enrollment = (Core::Enrollment).where(:student_id => person_id).limit(1)
    if !enrollment.empty?
      year_of_study = (enrollment.first[:semester] + 1) / 2
    end
    message = (Common::Pm::Message).where(:id => id, :person_id => person_id).first
    
    if message.scope == (Common::Pm::Message)::SCOPES[:all_users]
      result['for_person'] =  true
    end
    if message.scope == (Common::Pm::Message)::SCOPES[:all_students]
      result['for_person'] =  is_student
    end
    if message.scope == (Common::Pm::Message)::SCOPES[:all_teachers]
      result['for_person'] =  !is_student
    end
    if message.scope == (Common::Pm::Message)::SCOPES[:programme_students]
      result['for_person'] =  false if enrollment.empty?
      result['for_person'] =  (message[:to_id] == enrollment[:programme_id])
    end
    if message.scope == (Common::Pm::Message)::SCOPES[:academic_year_students]
      result['for_person'] =  false if enrollment.empty?
      result['for_person'] =  (this[:to_id] == year_of_study)
    end
    if message.scope == (Common::Pm::Message)::SCOPES[:course_unit_students]
      time_start = Time.mktime(current_year[:name].to_i, 9, 1, 0, 0, 0)
      if message[:time] < time_start
        result['for_person'] = false
      else
        portfolio = (Core::Portfolio).where(:person_id => person.id, :course_unit_id => message[:to_id], :academic_year_id => current_year[:id])
        result['for_person'] =  true
        result['for_person'] =  false if (!portfolio.empty?)
      end
    end
    if message.scope == (Common::Pm::Message)::SCOPES[:group_students]
      group = (Lms::Attendance::Group).joins(:student_groups => :person).where((Lms::Attendance::Group)::ID => message[:to_id], (Core::Person)::ID => person[:id])
      result['for_person'] = true
      result['for_person'] = false if (group.empty?)
    end
    if message.scope == (Common::Pm::Message)::SCOPES[:personal_message]
      result['for_person'] =  (message[:to_id] == person_id)
    end
    return result
  end
  
  
  def self.send_o(to_id, from_id, ref, subject, text)
    message = (Common::Pm::Message).new(:type => (Common::Pm::Message)::TYPES[:personal_message], :scope => (Common::Pm::Message)::SCOPES[:personal_message], :to_id => to_id, :from_id => from_id, :ref => ref, :subject => subject, :text => text)
    return message.save
  end
  
  
  def self.get_latest_for_person(person_id, limit)
    messages = (Common::Pm::Message).where(:type => (Common::Pm::Message)::TYPES[:personal_message], :to_id => person_id).order((Common::Pm::Message)::TIME + " DESC").limit(limit)
    return messages
  end
  
  
  def self.get_outbox_for_person(person_id, limit, start_from)
    messages = (Common::Pm::Message).where(:from_id => person_id, :type => (Common::Pm::Message)::TYPES[:personal_message]).order((Common::Pm::Message)::TIME + " DESC").order((Common::Pm::Message)::TIME).limit(limit).offset(start_from)
    
    return messages
  end
  
  
  
  # AnnouncementController
  def self.get_announcement_from_id(id)
    announcement = (Common::Pm::Message).where(:id => id, :type => 1).first
    return announcement
  end
  
  
  
  # AnnouncementController
  def self.get_latest_for_person(person_id, limit, is_student)
    current_year = (Core::AcademicYear).current_year
    enrollment = (Core::Enrollment).get_latest_for_student(person_id)
    current_year_of_study = (enrollment[:semester] + 1) / 2 if enrollment != nil
    
    all_announcements = (Common::Pm::Message).where(:type => (Common::Pm::Message)::TYPES[:announcement]).where([(Common::Pm::Message)::TIME + "> ?", 1.year.ago]).order((Common::Pm::Message)::TIME + ' DESC')
    announcements = []
    count = 0
    
    all_announcements.each do |announcement|
      next if announcement[:scope] == (Common::Pm::Message)::SCOPES[:all_students] and !is_student
      next if announcement[:scope] == (Common::Pm::Message)::SCOPES[:all_teachers] and is_student
      next if announcement[:scope] == (Common::Pm::Message)::SCOPES[:programme_students] and (enrollment == nil or announcement[:to_id] != enrollment[:programme_id])
      next if announcement[:scope] == (Common::Pm::Message)::SCOPES[:academic_year_students] and (enrollment == nil or announcement[:to_id] != enrollment[current_year_of_study])
      
      if announcement[:scope] == (Common::Pm::Message)::SCOPES[:course_unit_students]
        year_start = Time.mktime(current_year.to_i, 9, 1, 0, 0, 0)
        next if (announcement < year_start)
        next if (Core::Portfolio).from_course_unit(person_id, announcement[:to_id], current_year[:id]) == nil
        
        course_unit = (Core::CourseUnit).find(announcement[:to_id])
        to_text = course_unit[:name]
      end
      
      if announcement[:scope] == (Common::Pm::Message)::SCOPES[:group_students]
        group = (Lms::Attendance::Group).find(announcement[:to_id])
        next if group == nil
        next if (!(Lms::Attendance::Group).is_member(group[:id], student_id)['member'])
        
        to_text = group[:name]
      end
      
      next if announcement[:scope] == (Common::Pm::Message)::SCOPES[:personal_message] and announcement[:to_id] != person_id
      
      to_text = 'Administrator' if (to_text == "")
      
      announcement.to_id = to_text
      
      announcements << announcement
      break if announcements.count == limit
    end
    
    return announcements
  end
end

class Lms::Homework::Assignment < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'zadatak'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :homework_id, :zadaca
  # alias_attribute :assign_no, :redni_broj
  # alias_attribute :student_id, :student
  # alias_attribute :status, :status
  # alias_attribute :score, :bodova
  # alias_attribute :compile_report, :izvjestaj_skripte
  # alias_attribute :time, :vrijeme
  # alias_attribute :comment, :komentar
  # alias_attribute :filename, :filename
  # alias_attribute :author_id, :userid

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'zadatak'
  # ID =  TABLE_NAME + '.' + 'id'
  # HOMEWORK_ID =  TABLE_NAME + '.' + 'zadaca'
  # ASSIGN_NO =  TABLE_NAME + '.' + 'redni_broj'
  # STUDENT_ID =  TABLE_NAME + '.' + 'student'
  # STATUS =  TABLE_NAME + '.' + 'status'
  # SCORE =  TABLE_NAME + '.' + 'bodova'
  # COMPILE_REPORT =  TABLE_NAME + '.' + 'izvjestaj_skripte'
  # TIME =  TABLE_NAME + '.' + 'vrijeme'
  # COMMENT =  TABLE_NAME + '.' + 'komentar'
  # FILENAME =  TABLE_NAME + '.' + 'filename'
  # AUTHOR_ID =  TABLE_NAME + '.' + 'userid'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_homework_assignments'
  ID =  TABLE_NAME + '.' + 'id'
  HOMEWORK_ID =  TABLE_NAME + '.' + 'homework_id'
  ASSIGN_NO =  TABLE_NAME + '.' + 'assign_no'
  STUDENT_ID =  TABLE_NAME + '.' + 'student_id'
  STATUS =  TABLE_NAME + '.' + 'status'
  SCORE =  TABLE_NAME + '.' + 'score'
  COMPILE_REPORT =  TABLE_NAME + '.' + 'compile_report'
  TIME =  TABLE_NAME + '.' + 'time'
  COMMENT =  TABLE_NAME + '.' + 'comment'
  FILENAME =  TABLE_NAME + '.' + 'filename'
  AUTHOR_ID =  TABLE_NAME + '.' + 'author_id'

  ALL_COLUMNS = [ID, HOMEWORK_ID, ASSIGN_NO, STUDENT_ID, STATUS, SCORE, COMPILE_REPORT, TIME, COMMENT, FILENAME, AUTHOR_ID]
  
  belongs_to :homework
  belongs_to :student, :class_name => "Core::Person"
  belongs_to :author, :class_name => "Core::Person"  #Provjeriti o cemu se radi
  
  validates_presence_of :compile_report, :comment, :filename, :author_id
  
  
  def self.from_student_homework_number(author_id, homework_id, assign_no)
    assignment = (Lms::Homework::Assignment).where(:author_id => author_id, :homework_id => homework_id, :assign_no => assign_no).first
    
    return assignement
  end
  
  
end

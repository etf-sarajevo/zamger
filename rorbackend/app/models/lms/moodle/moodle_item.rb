class Lms::Moodle::MoodleItem
  attr_accessor :id, :type, :text, :url, :time_modified
  
  def self.get_latest_for_course(moodle_course_id)
    course_modules = (Lms::Moodle::MoodleCourseModule).where(["course = ?", moodle_course_id])
    items = []
    
    course_modules.each do |course_module|
      if (course_module["module"] == 9 and course_module["visible"] == 1)
        labels = (Lms::Moodle::MoodleLabel).where(:course => moodle_course_id, id => course_module['instance']).where(["timemodified > ?", 2.weeks.ago.to_i]).order('timemodified DESC')
        labels.each do |label|
          if course_module['added'] > label['timemodified']
            timemodified = course_module['added']
          else
            timemodified = label['timemodified']
          end
          items << (Lms::Moodle::MoodleItem).new(:type => "label", :text => labels['name'], :url => "", :timemodified => timemodified)
        end
      end
      
      
      if (course_module["module"] == 13 and course_module["visible"] == 1)
        resources = (Lms::Moodle::MoodleResource).where(:course => moodle_course_id, :id => course_module['instance']).order('timemodified DESC')
        
        resources.each do |resource|
          if course_module['added'] > label['timemodified']
            timemodified = course_module['added']
          else
            timemodified = label['timemodified']
          end
          
          items << (Lms::Moodle::MoodleItem).new(:type => "resource", :text => labels['name'], :url => ((Lms::Moodle)::URL + "mod/resource/view.php?id=#{course_module['id']}"), :timemodified => timemodified)
        end
      end
      
      return items
    end
  end
  
  private
  def initialize(hash)
    @type = hash[:type]
    @text = hash[:text]
    @url = hash[:url]
    @time_modified = hash[:time_modified]
  end
end

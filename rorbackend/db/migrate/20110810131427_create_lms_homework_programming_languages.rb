class CreateLmsHomeworkProgrammingLanguages < ActiveRecord::Migration
  def change
    create_table :lms_homework_programming_languages do |t|
      t.string :name, :limit => 50
      t.string :geshi, :limit => 20
      t.string :extension, :limit => 10

      # t.timestamps
    end
  end
end

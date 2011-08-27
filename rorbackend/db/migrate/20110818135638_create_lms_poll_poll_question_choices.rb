class CreateLmsPollPollQuestionChoices < ActiveRecord::Migration
  def change
    create_table :lms_poll_poll_question_choices do |t|
      t.integer :poll_question_id
      t.text :choice

      # t.timestamps
    end
  end
end
